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


	public function enviarCorreoPassword($user_email, $user_password):void{
		// Prepara el correo electrónico

		$headers['From']    = 'noresponder-iamon@hotmail.com';
		$headers['To']      = $user_email;
		$headers['Subject'] = 'Se ha restablecido tu contraseña';
		$body = 'Tu contraseña es : '.$user_password.' Si no has solicitado el cambio de contraseña, por favor, contacta con nosotros.';
		$params['host'] = '172.18.96.1'; //esta es la IP de la máquina host cuando se usa docker, allí hay un fakesmtp
		$params['port'] = '2525'; // puerto del fakesmtp
		// Create the mail object using the Mail::factory method
		$mail_object = Mail::factory('smtp', $params);
		$mail_object->send($user_email, $headers, $body);
		
	}

	public function updateUser($user_name, $user_new_password, $user_email) {
		try {
			$sql = "UPDATE users SET user_name = ?, user_password = ? WHERE user_email = ?";
			$stmt = $this->db->prepare($sql);

			//':user_new_password' => password_hash($user_new_password, PASSWORD_DEFAULT),
				
			$stmt->execute(array($user_name, $user_new_password, $user_email));
		} catch (PDOException $e) {
			throw new Exception("Database query error - " . $e->getMessage());
		}
	}

	public function deleteUser($user_name) {
		try {
			$sql = "DELETE FROM users WHERE user_name = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array($user_name));
		} catch (PDOException $e) {
			throw new Exception("Database query error - " . $e->getMessage());
		}
	}

	
}
