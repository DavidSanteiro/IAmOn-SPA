<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");
require_once(__DIR__."/BaseRest.php");

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

	public function postUser($data) {
		$user = new User($data->username, $data->password);
		try {
			$user->checkIsValidForRegister();

			$this->userMapper->save($user);

			header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
			header("Location: ".$_SERVER['REQUEST_URI']."/".$data->username);
		}catch(ValidationException $e) {
			http_response_code(400);
			header('Content-Type: application/json');
			echo(json_encode($e->getErrors()));
		}
	}

	

	public function login($user_name, $user_password) {
		// Comprueba si el usuario existe
	
		try {
			// Verifica si la contraseña es correcta
			if ($this->userMapper->isValidUser($user_name, $user_password)) {
				// Genera un bearer_token y lo establece como una cookie
				$bearer_token = $this->generateBearerToken();
				setcookie('bearer_token', $bearer_token, time() + (86400 * 30), "/"); // 86400 = 1 day
	
				// Devuelve una respuesta HTTP 200
				parent::answerJson200(array(
					"user_name" => $user_name,
					"bearer_token" => $bearer_token
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
	
	private function generateBearerToken() {
		// Genera un token aleatorio
		$token = bin2hex(openssl_random_pseudo_bytes(16));
	
		return $token;
	}


	public function register($user_name, $user_email, $user_password) {
		
		try {
			// Comprueba si el nombre de usuario o el correo electrónico ya existen
			if ($this->userMapper->userEmailExists($user_email)) {
				// Si existen, devuelve una respuesta HTTP 409
				parent::error409("El correo electrónico ya existe.");
			} else if ($this->userMapper->usernameExists($user_name)) {
				// Si existen, devuelve una respuesta HTTP 409
				parent::error409("El nombre de usuario ya existe.");
			}else {
				// Si no existen, inserta el nuevo usuario en la base de datos
				$user = new User($user_name, $user_email, $user_password);
				$this->userMapper->save($user);
	
				// Devuelve una respuesta HTTP 201
				parent::answerJson201(array("user_name" => $user_name, "user_email" => $user_email));
			}
		} catch (Exception $e) {
			// Si la base de datos falla o hay un error imprevisto
			parent::error500();
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

	public function resetPassword ($user_email){
		// Comprueba si el usuario existe
		try {
			// Verifica si la contraseña es correcta
			if ($this->userMapper->userEmailExists($user_email)) {
				// Si existe, devuelve una respuesta HTTP 200 porque lo encontró
				$user_password = $this->userMapper->recuperarPassword($user_email);
				$this->userMapper->enviarCorreoPassword($user_email, $user_password);
				parent::answerJson200(array("user_email" => $user_email));
			} else {
				// Si el email no existe, devuelve una respuesta HTTP 401
				parent::error404("Email no existente.");
			}
		} catch (Exception $e) {
			// Si la base de datos falla o hay un error imprevisto
			parent::error500();
		}
	
	}

	public function editAccount($user_name, $user_password, $user_new_password, $user_email){
		try {
			if ($this->userMapper->userEmailExists($user_email)) {
				$current_password = $this->userMapper->recuperarPassword($user_email);
				if ($current_password == $user_password) {
					$this->userMapper->updateUser($user_name, $user_new_password, $user_email);
					parent::answerJson200(array("user_email" => $user_email));
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

	public function deleteAccount($user_name, $user_password){
		try {
			if ($this->userMapper->usernameExists($user_name)) {
				$user_email = $this->userMapper->recuperarEmail($user_name);
				$current_password = $this->userMapper->recuperarPassword($user_email);
				if ($current_password == $user_password) {
					$this->userMapper->deleteUser($user_name);
					parent::answerJson204(array("user_name" => $user_name));
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
	
}



// URI-MAPPING for this Rest endpoint
$userRest = new UserRest();
URIDispatcher::getInstance()
->map("POST", "/account", array($userRest,"login"))
->map("POST", "/account/new", array($userRest,"register"))
->map("GET", "/account/userAvailability/$1", array($userRest,"checkUserAvailability"))
->map("POST", "/account/passwordReset", array($userRest,"resetPassword"))
->map("PUT", "/account", array($userRest,"editAccount"))
->map("DELETE", "/account", array($userRest,"deleteAccount"));