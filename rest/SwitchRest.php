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
		parent::error503("toggleButtonPublic Not implemented");

		$currentUser = parent::authenticateUser();

		// Get data from HTTP message
		$public_uuid = $data->switch_public_uuid;
		$tempSwitchedOn = filter_var($data->minutes_on, FILTER_VALIDATE_INT);

		// Verify the values are valid
		if (!preg_match(SwitchMapper::REGEX_UUID, $public_uuid)) {
			parent::error400(array("switch_public_uuid" => "la variable no es un UUID."));
		}
		if(!$tempSwitchedOn){
			parent::error400(array("minutes_on"=>"switch on time must be an number. Value received".$tempSwitchedOn));
		}
		if ($tempSwitchedOn > 120 || $tempSwitchedOn < 0){
			parent::error400(array("minutes_on"=>"switch on time must be upper than zero. Value received".$tempSwitchedOn));
		}

		// Does the switch exist?
		$switch = $this->switchMapper->findByPublicUUID($public_uuid);
		if ($switch == NULL) {
			parent::error404("No such switch with public uuid: ".$public_uuid);
		}

		// Check if the Switch owner is the currentUser (in Session)
		if ($switch->getOwner()->getUsername() != $currentUser->getUsername()) {
			parent::error403("Logged user is not the author of the switch uuid ".$public_uuid);
		}

		// Power on (time_on > 0) or off (time_on = 0)
		if($tempSwitchedOn > 0){
			$switchPowerOffDate = new DateTime();
			$switchPowerOffDate->modify("+".$tempSwitchedOn." minutes");

			$switch->setPowerOff($switchPowerOffDate);
			$switch->setLastPowerOn(new DateTime());
		}else{
			$switch->setPowerOff(new DateTime());
		}

		//TODO incorporar las funciones de correo
//		// Enviar email a los suscriptores
//		$suscribers = $this->switchMapper->phpEmail($switch->getPublicUuid());
//		$nombre_switch = $switch->getSwitchName();
//		$user_switch_name = $switch->getOwner()->getUsername();
//		foreach ($suscribers as $suscriber){
//			$this->enviarCorreo($suscriber->getEmail(), $suscriber->getUsername(), $nombre_switch, $user_switch_name);
//		}

		try {
			// Validate Switch object
			$switch->checkIsValidForUpdate(); // if it fails, ValidationException

			// Update the Switch object in the database
			if(!$this->switchMapper->update($switch)){
				parent::error500();
			}

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		}catch(ValidationException $ex) {
			// If can't validate data
			// TODO no se que poner aquí. Si entra aquí no valida pero no es culpa del usuario (DUDA)
			parent::error400($ex->getErrors());

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function toggleButtonPrivate($data){
		//TODO
		parent::error503("toggleButtonPrivate Not implemented");

		// Get data from HTTP message
		$private_uuid = $data->switch_private_uuid;
		$tempSwitchedOn = filter_var($data->minutes_on, FILTER_VALIDATE_INT);

		// Verify the values are valid
		if (!preg_match(SwitchMapper::REGEX_UUID, $private_uuid)) {
			parent::error400(array("switch_private_uuid" => "La variable no es un UUID."));
		}
		if(!$tempSwitchedOn){
			parent::error400(array("minutes_on"=>"switch on time must be an number. Value received".$tempSwitchedOn));
		}
		if ($tempSwitchedOn > 120 || $tempSwitchedOn < 0){
			parent::error400(array("minutes_on"=>"switch on time must be upper than zero. Value received".$tempSwitchedOn));
		}

		// Does the switch exist?
		$switch = $this->switchMapper->findByPrivateUUID($private_uuid);
		if ($switch == NULL) {
			parent::error404("No such switch with private uuid: ".$private_uuid);
		}

		// Power on (time_on > 0) or off (time_on = 0)
		if($tempSwitchedOn > 0){
			$switchPowerOffDate = new DateTime();
			$switchPowerOffDate->modify("+".$tempSwitchedOn." minutes");

			$switch->setPowerOff($switchPowerOffDate);
			$switch->setLastPowerOn(new DateTime());
		}else{
			$switch->setPowerOff(new DateTime());
		}

		//TODO incorporar las funciones de correo
//		// Enviar email a los suscriptores
//		$suscribers = $this->switchMapper->phpEmail($switch->getPublicUuid());
//		$nombre_switch = $switch->getSwitchName();
//		$user_switch_name = $switch->getOwner()->getUsername();
//		foreach ($suscribers as $suscriber){
//			$this->enviarCorreo($suscriber->getEmail(), $suscriber->getUsername(), $nombre_switch, $user_switch_name);
//		}

		try {
			// Validate Switch object
			$switch->checkIsValidForUpdate(); // if it fails, ValidationException

			// Update the Switch object in the database
			if(!$this->switchMapper->update($switch)){
				parent::error500();
			}

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		}catch(ValidationException $ex) {
			// If can't validate data
			// TODO no se que poner aquí. Si entra aquí no valida pero no es culpa del usuario (DUDA)
			parent::error400($ex->getErrors());

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function add($data){
		//TODO
		parent::error503("add Not implemented");

		$currentUser = parent::authenticateUser();

		// Get data from HTTP message
		$switch_name = $data->switch_name;
		$switch_description = $data->switch_description;

		// The user of the Switch is the currentUser (user in session)
		$switch->setOwner($currentUser);

		$switch->setSwitchName($switch_name);
		$switch->setDescription($switch_description);

		try {
			// Validate switch object
			$switch->checkIsValidForCreate(); // if it fails, ValidationException

			// Save the switch object into the database
			$switch_public_uuid = $this->switchMapper->save($switch);

			// Bring all switch attributes from database
			$switch = $this->switchMapper->findByPublicUUID($switch_public_uuid);

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_private_uuid" => $switch->getPrivateUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		}catch(ValidationException $ex) {
			// If can't validate data
			parent::error400($ex->getErrors());
		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function edit($data){
		//TODO
		parent::error503("edit Not implemented");

		$currentUser = parent::authenticateUser();

		// Get data from HTTP message
		$switch_public_uuid = $data->switch_public_uuid;
		$switch_name = $data->switch_name;
		$switch_description = $data->switch_description;
		$reset_switch_private_uuid = filter_var($data->reset_switch_private_uuid, FILTER_VALIDATE_BOOL);

		if (!preg_match(SwitchMapper::REGEX_UUID, $switch_public_uuid)) {
			parent::error400(array("switch_public_uuid" => "La variable no es un UUID."));
		}

		// Get the switch from database and check if exists
		$switch = $this->switchMapper->findByPublicUUID($switch_public_uuid);
		if(is_null($switch)){
			parent::error404("There's no switch with that switch_public_uuid");
		}

		if($switch->getOwner()->getUsername() != $currentUser->getUsername()){
			parent::error403("Logged user is not the author of the switch uuid ".$public_uuid);
		}

		$switch->setSwitchName($switch_name);
		$switch->setDescription($switch_description);
		if($reset_switch_private_uuid) $switch->setPrivateUuid($this->switchMapper->generateUUID());

		try {
			// Validate switch object
			$switch->checkIsValidForUpdate(); // if it fails, ValidationException

			// Save the switch object into the database
			if($this->switchMapper->update($switch) != 1){
				parent::error500();
			}

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_private_uuid" => $switch->getPrivateUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		}catch(ValidationException $ex) {
			// If can't validate data
			parent::error400($ex->getErrors());
		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function delete($data){
		//TODO
		parent::error503("delete Not implemented");

		$currentUser = parent::authenticateUser();

		// Get data from HTTP message
		$switch_public_uuid = $data->switch_public_uuid;

		if (!preg_match(SwitchMapper::REGEX_UUID, $switch_public_uuid)) {
			parent::error400(array("switch_public_uuid" => "La variable no es un UUID."));
		}

		// Get the switch from database and check if exists
		$switch = $this->switchMapper->findByPublicUUID($switch_public_uuid);
		if(is_null($switch)){
			parent::error404("There's no switch with that switch_public_uuid");
		}

		if($switch->getOwner()->getUsername() != $currentUser->getUsername()){
			parent::error403("Logged user is not the author of the switch uuid ".$public_uuid);
		}

		try {

			// Delete the switch object from the database
			if($this->switchMapper->delete($switch) != 1){
				parent::error500();
			}

			parent::answerString204("Switch with public UUID ".$switch_public_uuid." successfully deleted");

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function list($user_name){
		//TODO
		parent::error503("list Not implemented");

		$currentUser = parent::authenticateUser();

		if($user_name != $currentUser->getUsername()){
			parent::error403("Logged user is not the user specified");
		}

		// Get the switches from database
		$switches = $this->switchMapper->findMySwitches($currentUser);

		try {
			$arrayUserSwitches = array();
			foreach ($switches as $switch){
				$arrayUserSwitches[] = [
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_private_uuid" => $switch->getPrivateUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff()
				];
			}

			parent::answerJson200($arrayUserSwitches);

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function listSubscribed($user_name){
		//TODO
		parent::error503("listSubscribed Not implemented");

		$currentUser = parent::authenticateUser();

		if($user_name != $currentUser->getUsername()){
			parent::error403("Logged user is not the user specified");
		}

		// Get the switches from database
		$switches = $this->switchMapper->findSuscribedSwitches($currentUser);

		try {
			$arrayUserSubscribedSwitches = array();
			foreach ($switches as $switch){
				$arrayUserSubscribedSwitches[] = [
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff()
				];
			}

			parent::answerJson200($arrayUserSubscribedSwitches);

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function getPublic($switch_public_uuid){
		//TODO
		parent::error503("getPublic Not implemented");

		if (!preg_match(SwitchMapper::REGEX_UUID, $switch_public_uuid)) {
			parent::error400(array("switch_public_uuid" => "La variable no es un UUID."));
		}

		// Get the switches from database
		$switch = $this->switchMapper->findByPublicUUID($switch_public_uuid);
		if ($switch == NULL) {
			parent::error404("No such switch with public uuid: ".$switch_public_uuid);
		}

		try {

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
	}

	public function getPrivate($switch_private_uuid){
		//TODO
		parent::error503("getPrivate Not implemented");

		if (!preg_match(SwitchMapper::REGEX_UUID, $switch_private_uuid)) {
			parent::error400(array("switch_private_uuid" => "La variable no es un UUID."));
		}

		// Get the switches from database
		$switch = $this->switchMapper->findByPrivateUUID($switch_private_uuid);
		if ($switch == NULL) {
			parent::error404("No such switch with private uuid: ".$switch_private_uuid);
		}

		try {

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_private_uuid" => $switch->getPrivateUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
			parent::error500();
		}
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
