<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");
require_once(__DIR__."/BaseRest.php");

require_once '../vendor/autoload.php';

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

/**
* Class UserRest
*
* It contains operations for adding and check users credentials.
* Methods gives responses following Restful standards. Methods of this class
* are intended to be mapped as callbacks using the URIDispatcher class.
*
*/
class UserRest extends BaseRest {
	private $userMapper;

	public function __construct() {
		parent::__construct();

		$this->userMapper = new UserMapper();
	}

	public function login($data) {
		// Comprueba si el usuario existe
	
		try {
			// Verifica si la contraseña es correcta
			if ($this->userMapper->isValidUser($data->user_name, $data->user_password)) {

                $payload = [
                    'iss' => 'http://localhost',
                    'aud' => $data->user_name,
                    'iat' => time(),
                    'exp' => time() + (86400 * 30) // Los tokens expiran en 1 día
                ];

                $jwt_token = JWT::encode($payload, BaseRest::$jwt_key, 'HS256');

				// Devuelve una respuesta HTTP 200
				parent::answerJson200(array(
					"user_name" => $data->user_name,
					"jwt_token" => $jwt_token
				));

			} else {
				// Si la contraseña es incorrecta, devuelve una respuesta HTTP 401
				parent::error401("Contraseña incorrecta o usuario no existente.");
			}
		} catch (Exception $e) {
			// Si la base de datos falla o hay un error imprevisto
			parent::error500();
		}
	}

	public function register($data) {
		
		try {
			// Comprueba si el nombre de usuario o el correo electrónico ya existen
			if (is_null($data->user_email) && $this->userMapper->userEmailExists($data->user_email)) {
				// Si existen, devuelve una respuesta HTTP 409
				parent::error409("El correo electrónico ya existe.");
			}
            if ($this->userMapper->usernameExists($data->user_name)) {
				// Si existen, devuelve una respuesta HTTP 409
				parent::error409("El nombre de usuario ya existe.");
			}

            // Si no existen, inserta el nuevo usuario en la base de datos
            $user = new User($data->user_name, $data->user_password, $data->user_email);
            $this->userMapper->save($user);

            // Devuelve una respuesta HTTP 201
            parent::answerJson201(array("user_name" => $data->user_name, "user_email" => $data->user_email));

		} catch (Exception $e) {
			// Si la base de datos falla o hay un error imprevisto
			parent::error500($e);
		}
	}

	public function checkUserAvailability($user_name) {
		try {
			// Comprueba si el nombre de usuario ya existe
			if ($this->userMapper->usernameExists($user_name)) {
				// Si existe, devuelve una respuesta HTTP 200 porque lo encontró
				parent::answerJson200(array("user_name" => $user_name));
				
			} else {
				// Si no existe, devuelve una respuesta HTTP 409
				parent::error404("El nombre de usuario no existe.");
			}
		} catch (Exception $e) {
			// Si la base de datos falla o hay un error imprevisto
			parent::error500();
		}
	}

	public function resetPassword($data){
		// Comprueba si el usuario existe
		try {
			// Verifica si la contraseña es correcta
			if ($this->userMapper->userEmailExists($data->user_email)) {

                $arraySecurityElems = $this->userMapper->getSecurityElementsFromEmail($data->user_email);
                $securityCode = $arraySecurityElems[0];

                $securityCodeDate = $arraySecurityElems[1]->modify("+ 10 minutes");

                $currentTime = new DateTime();

                if ($securityCode == $data->security_code){
                    if ($securityCodeDate < $currentTime){
                        $this->error403("El código ha caducado.");
                    }else{
                        $user = $this->userMapper->getUserFromEmail($data->user_email);
                        $this->userMapper->updateUser($user->getUsername(), $data->user_password, $user->getEmail());
                        parent::answerString200("Se ha cambiado correctamente la contraseña. Ahora puedes iniciar sesión.");
                    }
                }else{
                    parent::error403("Código no registrado");
                }
			} else {
				// Si el email no existe, devuelve una respuesta HTTP 401
				parent::error404("Email no existente.");
			}
		} catch (Exception $e) {
			// Si la base de datos falla o hay un error imprevisto
			parent::error500();
		}
	}

    public function generateSecurityCode($data) {

        try {
            // Comprueba si el nombre de usuario o el correo electrónico ya existen
            if (is_null($data->user_email) || !$this->userMapper->userEmailExists($data->user_email)) {
                // Si no existe, devuelve una respuesta HTTP 404
                parent::error404("El correo electrónico no existe.");
            }
            $user = $this->userMapper->getUserFromEmail($data->user_email);
            $this->userMapper->generateAndSendSecurityCode($user);

            parent::answerString200("Te hemos enviado un correo. Introduce el código que te hemos enviado.");

        } catch (Exception $e) {
            // Si la base de datos falla o hay un error imprevisto
            parent::error500($e->getMessage());
        }
    }

	public function editAccount($data){
		try {
			if ($this->userMapper->userEmailExists($data->user_email)) {
				$current_password = $this->userMapper->recuperarPassword($data->user_email);
				if ($current_password == $data->user_password) {
					$this->userMapper->updateUser($data->user_name, $data->user_new_password, $data->user_email);
					parent::answerJson200(array("user_email" => $data->user_email));
				} else {
					parent::error403("Forbidden");
				}
			} else {
				parent::error404("Email no existente.");
			}
		} catch (Exception $e) {
			parent::error500();
		}
	}

	public function deleteAccount($data){
		try {
			if ($this->userMapper->usernameExists($data->user_name)) {
				$user_email = $this->userMapper->recuperarEmail($data->user_name);
				$current_password = $this->userMapper->recuperarPassword($user_email);
				if ($current_password == $data->user_password) {
					$this->userMapper->deleteUser($data->user_name);
					parent::answerJson204(array("user_name" => $data->user_name));
				} else {
					parent::error401("Unauthorized");
				}
			} else {
				parent::error404("User not found.");
			}
		} catch (Exception $e) {
			parent::error500();
		}
	}

    public function checkIfNotExpired($data){
        try {
            $token = $data->jwt_token;

            $decoded_array = (array) JWT::decode($token, new Key(BaseRest::$jwt_key, 'HS256'));

            $userMapper = new UserMapper();

            if (!empty($decoded_array["aud"]) && $userMapper->usernameExists($decoded_array["aud"])) {

                if($decoded_array["aud"] != $data->user_name){
                    parent::error403("Token user_name mismatch");
                }

                $toRet = array();
                $toRet[] = [
                    "user_name" => $decoded_array["aud"]
                ];
                parent::answerJson200($toRet);

            }else{
                $this->error401('Invalid token');
            }

        }catch (ExpiredException $exception){
            $this->error401($exception->getMessage());
        }catch (SignatureInvalidException $exception) {
            $this->error400($exception->getMessage());
        }catch (UnexpectedValueException $exception){
            $this->error400($exception->getMessage());
        }catch (Exception $exception){
            $this->error500();
        }
    }
	
}



// URI-MAPPING for this Rest endpoint
$userRest = new UserRest();
URIDispatcher::getInstance()
->map("POST", "/account", array($userRest,"login"))
->map("POST", "/account/new", array($userRest,"register"))
->map("PUT", "/account", array($userRest,"editAccount"))
->map("DELETE", "/account", array($userRest,"deleteAccount"))
->map("PUT", "/account/checkToken", array($userRest,"checkIfNotExpired"))
->map("GET", "/account/userAvailability/$1", array($userRest,"checkUserAvailability"))
->map("POST", "/account/password", array($userRest,"resetPassword"))
->map("PUT", "/account/password", array($userRest,"generateSecurityCode"));
