<?php

require_once(__DIR__."/../model/Switch.php");
require_once(__DIR__."/../model/SwitchMapper.php");
require_once(__DIR__."/BaseRest.php");
require_once (__DIR__."/URIDispatcher.php");
require_once("/usr/share/php/Mail.php");

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

		// Enviar email a los suscriptores
		$suscribers = $this->switchMapper->phpEmail($switch->getPublicUuid());
		$nombre_switch = $switch->getSwitchName();
		$user_switch_name = $switch->getOwner()->getUsername();
		foreach ($suscribers as $suscriber){
			$this->enviarCorreo($suscriber->getEmail(), $suscriber->getUsername(), $nombre_switch, $user_switch_name);
		}

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

		// Enviar email a los suscriptores
		$suscribers = $this->switchMapper->phpEmail($switch->getPublicUuid());
		$nombre_switch = $switch->getSwitchName();
		$user_switch_name = $switch->getOwner()->getUsername();
		foreach ($suscribers as $suscriber){
			$this->enviarCorreo($suscriber->getEmail(), $suscriber->getUsername(), $nombre_switch, $user_switch_name);
		}

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

		$currentUser = parent::authenticateUser();

		// Get data from HTTP message
		$switch_name = $data->switch_name;
		$switch_description = $data->switch_description;

		// The user of the Switch is the currentUser (user in session)
        $public_uuid = $this->switchMapper->generateUUID();
        $switch = new MySwitch($switch_name, $currentUser, $public_uuid, NULL,
										$switch_description, NULL, NULL);

		try {
			// Validate switch object
			$switch->checkIsValidForCreate(); // if it fails, ValidationException

			// Save the switch object into the database
			$this->switchMapper->save($switch);
			// Bring all switch attributes from database
			$switch = $this->switchMapper->findByPublicUUID($public_uuid);

			parent::answerJson200(array(
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_private_uuid" => $switch->getPrivateUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switch->getLastPowerOn()?->format(SwitchMapper::DATE_FORMAT),
					"switch_power_off" => $switch->getPowerOff())
			);

		}catch(ValidationException $ex) {
			// If can't validate data
			parent::error400($ex->getErrors());
		} catch (Exception $e) {
			// If database fails or there is an unforeseen error
            echo $e->getMessage();
			parent::error500($e);
		}
	}

	public function edit($data){

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
			parent::error403("Logged user is not the author of the switch uuid ".$switch_public_uuid);
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
			parent::error403("Logged user is not the author of the switch uuid ".$switch_public_uuid);
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
					"switch_last_power_on" =>
                        is_null($switch->getLastPowerOn()) ? null :
                            $switch->getLastPowerOn()->format(SwitchMapper::DATE_FORMAT),
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

		$currentUser = parent::authenticateUser();

		if($user_name != $currentUser->getUsername()){
			parent::error403("Logged user is not the user specified");
		}

		// Get the switches from database
		$switches = $this->switchMapper->findSuscribedSwitches($currentUser);

		try {
			$arrayUserSubscribedSwitches = array();
			foreach ($switches as $switch){

			    $lastPowerOn = $switch->getLastPowerOn();
                $switchLastPowerOn = ($lastPowerOn !== null) ? $lastPowerOn->format(SwitchMapper::DATE_FORMAT) : null;

				$arrayUserSubscribedSwitches[] = [
					"switch_public_uuid" => $switch->getPublicUuid(),
					"switch_name" => $switch->getSwitchName(),
					"switch_description" => $switch->getDescription(),
					"switch_last_power_on" => $switchLastPowerOn,
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

	private function enviarCorreo($user_email,$user_name,$switch_name,$user_switch_name):void{
		// Reemplaza esto con tu dirección de correo electrónico

		$headers['From']    = 'noresponder-iamon@hotmail.com';
		$headers['To']      = $user_email;
		$headers['Subject'] = 'Un switch de '.$user_switch_name.' se ha encendido!';
		$body = $user_name.' el switch '.$switch_name.' al que estas suscrito, ahora está encendido!';
		$params['host'] = '172.18.96.1'; //esta es la IP de la máquina host cuando se usa docker, allí hay un fakesmtp
		$params['port'] = '2525'; // puerto del fakesmtp
		// Create the mail object using the Mail::factory method
		$mail_object = Mail::factory('smtp', $params);
		$mail_object->send($user_email, $headers, $body);
	}
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
