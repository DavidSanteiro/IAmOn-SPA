<?php

require_once(__DIR__."/../model/Switch.php");
require_once(__DIR__."/../model/SwitchMapper.php");
require_once(__DIR__."/BaseRest.php");

/**
 * Class SwitchRest
 *
 * It contains operations for adding and returning switches information.
 * Methods gives responses following Restful standards. Methods of this class
 * are intended to be mapped as callbacks using the URIDispatcher class.
 *
 */
class SwitchRest extends BaseRest {
	private $switchMapper;

	public function __construct() {
		parent::__construct();

		$this->switchMapper = new SwitchMapper();
	}

	public function toggleButtonPublic($data){
		//TODO
		throw new RuntimeException("toggleButtonPublic Not implemented");
	}

	public function toggleButtonPrivate($data){
		//TODO
		throw new RuntimeException("toggleButtonPrivate Not implemented");
	}

	public function add($data){
		//TODO
		throw new RuntimeException("add Not implemented");
	}

	public function edit($data){
		//TODO
		throw new RuntimeException("edit Not implemented");
	}

	public function delete($data){
		//TODO
		throw new RuntimeException("delete Not implemented");
	}

	public function list($data){
		//TODO
		throw new RuntimeException("list Not implemented");
	}

	public function listSubscribed($data){
		//TODO
		throw new RuntimeException("listSubscribed Not implemented");
	}

	public function getPublic($data){
		//TODO
		throw new RuntimeException("getPublic Not implemented");
	}

	public function getPrivate($data){
		//TODO
		throw new RuntimeException("getPrivate Not implemented");
	}

//	public function postUser($data) {
//		$user = new User($data->username, $data->password);
//		try {
//			$user->checkIsValidForRegister();
//
//			$this->switchMapper->save($user);
//
//			header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
//			header("Location: ".$_SERVER['REQUEST_URI']."/".$data->username);
//		}catch(ValidationException $e) {
//			http_response_code(400);
//			header('Content-Type: application/json');
//			echo(json_encode($e->getErrors()));
//		}
//	}
//
//	public function login($username) {
//		$currentLogged = parent::authenticateUser();
//		if ($currentLogged->getUsername() != $username) {
//			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
//			echo("You are not authorized to login as anyone but you");
//		} else {
//			header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
//			echo("Hello ".$username);
//		}
//	}
}

// URI-MAPPING for this Rest endpoint
$switchRest = new SwitchRest();
URIDispatcher::getInstance()
	->map("POST",	"/switch/public", array($switchRest,"toggleButtonPublic"))
	->map("POST",	"/switch/private", array($switchRest,"toggleButtonPrivate"))
	->map("POST",	"/switch/new", array($switchRest,"add"))
	->map("PUT",	"/switch", array($switchRest,"edit"))
	->map("DELETE",	"/switch", array($switchRest,"delete"))
	->map("GET",	"/switch/$1", array($switchRest,"list"))
	->map("GET",	"/switch/subscription/$1", array($switchRest,"listSubscribed"))
	->map("GET",	"/switch/public/$1", array($switchRest,"getPublic"))
	->map("GET",	"/switch/private/$1", array($switchRest,"getPrivate"));
