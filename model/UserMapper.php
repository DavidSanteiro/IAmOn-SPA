<?php
// file: model/UserMapper.php

require_once(__DIR__."/../core/PDOConnection.php");

/**
* Class UserMapper
*
* Database interface for User entities
*
* @author lipido <lipido@gmail.com>
*/
class UserMapper {

	/**
	* Reference to the PDO connection
	* @var PDO
	*/
	private $db;

	public function __construct() {
		$this->db = PDOConnection::getInstance();
	}

	/**
	* Saves a User into the database
	*
	* @param User $user The user to be saved
	* @throws PDOException if a database error occurs
	* @return void
	*/
	public function save($user) : void {
		$stmt = $this->db->prepare("INSERT INTO User (user_name, user_password, user_email) values (?,?,?)");
		$stmt->execute(array($user->getUsername(), $user->getPasswd(), $user->getEmail()));
	}

	/**
	* Checks if a given username is already in the database
	*
	* @param string $username the username to check
	* @return boolean true if the username exists, false otherwise
	*/
	public function usernameExists($username) {
		$stmt = $this->db->prepare("SELECT count(*) FROM User where user_name=?");
		$stmt->execute(array($username));

		if ($stmt->fetchColumn() > 0) {
			return true;
		}
		return false;
	}


	public function userEmailExists($user_email) {
		$stmt = $this->db->prepare("SELECT count(*) FROM User where user_email=?");
		$stmt->execute(array($user_email));

		if ($stmt->fetchColumn() > 0) {
			return true;
		}
		return false;
	}

	/**
	* Checks if a given pair of username/password exists in the database
	*
	* @param string $username the username
	* @param string $passwd the password
	* @return boolean true the username/passwrod exists, false otherwise.
	*/
	public function isValidUser($username, $passwd) {
		$stmt = $this->db->prepare("SELECT count(user_name) FROM User where user_name=? and user_password=?");
		$stmt->execute(array($username, $passwd));

		if ($stmt->fetchColumn() > 0) {
			return true;
		}
		return false;
	}


	public function recuperarEmail($user_name): array {
		$stmt = $this->db->prepare("SELECT User.user_email FROM User WHERE User.user_name=? ");
		$stmt->execute(array($user_name));

		$user_email = null;
		if ($row = $stmt->fetch()) {
			$user_email = $row["user_email"];
		}
		return $user_email;
	}


	public function recuperarPassword($user_email): array {
		$stmt = $this->db->prepare("SELECT User.user_password FROM User WHERE User.user_email=? ");
		$stmt->execute(array($user_email));

		$user_password = null;
		if ($row = $stmt->fetch()) {
			$user_password = $row["user_password"];
		}
		return $user_password;
	}

    public function getUserFromEmail($user_email): ?User {
        $stmt = $this->db->prepare("SELECT User.user_name FROM User WHERE User.user_email=? ");
        $stmt->execute(array($user_email));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user != null) {
            return new User(
                $user["user_name"],
                null,
                $user_email);
        }
        return null;
    }

    public function generateAndSendSecurityCode($user) : void {
        $currentDate = new DateTime();
        $securityCode = mt_rand(100000, 999999); // Número aleatorio de 6 dígitos;

        $stmt = $this->db->prepare("UPDATE User SET security_code = ?, security_code_date = ?  WHERE user_name = ?");
        $stmt->execute(array(
            $securityCode,
            $currentDate->format('Y-m-d H:i:s'),
            $user->getUsername()));

        // Prepara el correo electrónico
        $headers['From']    = 'noresponder-iamon@hotmail.com';
        $headers['To']      = $user->getEmail();
        $headers['Subject'] = 'Se ha restablecido tu contraseña';
        $body = 'Tu código de seguridad es : "'.$securityCode.'". La validez del código es de 10 minutos.
        Si no has solicitado el cambio de contraseña, por favor, contacta con nosotros.';
        $params['host'] = '192.168.1.51'; //esta es la IP de la máquina host cuando se usa docker, allí hay un fakesmtp
        $params['port'] = '2525'; // puerto del fakesmtp
        // Create the mail object using the Mail::factory method
        $mail_object = Mail::factory('smtp', $params);
        $mail_object->send($user->getEmail(), $headers, $body);
    }

    public function getSecurityElementsFromEmail($user_email): ?array {
        $stmt = $this->db->prepare("SELECT User.security_code, User.security_code_date FROM User WHERE User.user_email=? ");
        $stmt->execute(array($user_email));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if($data != null) {
            $toRet = array();
                array_push($toRet, $data["security_code"], new DateTime($data["security_code_date"]));
                return $toRet;
        }
        return null;
    }

	public function updateUser($user_name, $user_new_password, $user_email) {
		try {
			$sql = "UPDATE User SET user_name = ?, user_password = ? WHERE user_email = ?";
			$stmt = $this->db->prepare($sql);

			//':user_new_password' => password_hash($user_new_password, PASSWORD_DEFAULT),
				
			$stmt->execute(array($user_name, $user_new_password, $user_email));
		} catch (PDOException $e) {
			throw new Exception("Database query error - " . $e->getMessage());
		}
	}

	public function deleteUser($user_name) {
		try {
			$sql = "DELETE FROM User WHERE user_name = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array($user_name));
		} catch (PDOException $e) {
			throw new Exception("Database query error - " . $e->getMessage());
		}
	}

	
}
