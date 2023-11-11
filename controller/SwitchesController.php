<?php
//file: controller/SwitchesController.php

require_once(__DIR__ . "/../model/Switch.php");
require_once(__DIR__ . "/../model/SwitchMapper.php");
require_once(__DIR__."/../model/User.php");

require_once(__DIR__."/../core/ViewManager.php");
require_once(__DIR__."/../controller/BaseController.php");
require_once("/usr/share/php/Mail.php");

/**
* Class SwitchesController
*
* Controller to make a CRUDL of Switch entities
*
* @author lipido <lipido@gmail.com>
*/
class SwitchesController extends BaseController {

	/**
	* Reference to the SwitchMapper to interact
	* with the database
	*
	* @var SwitchMapper
	*/
	private SwitchMapper $switchMapper;

	public function __construct() {
		parent::__construct();

		$this->switchMapper = new SwitchMapper();

		$this->view->setLayout("header_logged_in");
	}

	/**
	* Action to list switches
	*
	* Loads all the switches from the user in the database.
	* No HTTP parameters are needed.
	*
	* The views are:
	* <ul>
	* <li>switches/dashboard (via include)</li>
	* </ul>
	*/
	public function index(): void {

		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Seeing your switches requires login");
		}

		// obtain the data from the database
		$userSwitches = $this->switchMapper->findMySwitches($this->currentUser);
		$userSubscribedSwitches = $this->switchMapper->findSuscribedSwitches($this->currentUser);

		// put the array containing Switches objects to the view
		$this->view->setVariable("userSwitches", $userSwitches);
		$this->view->setVariable("userSubscribedSwitches", $userSubscribedSwitches);

		// render the view (/view/switches/dashboard.php)
		$this->view->render("switches", "dashboard");
	}

	/**
	 * Action to view a given switch (with public uuid or private uuid)
	 *
	 * This action should only be called via GET
	 *
	 * The expected HTTP parameters are:
	 * <ul>
	 * 	<li>public_uuid: Public UUID of the switch (via HTTP GET)</li>
	 * 	<li>private_uuid: Private UUID of the switch (via HTTP GET)</li>
	 * </ul>
	 *
	 * The views are:
	 * <ul>
	 * 	<li>switches/view: If switch is successfully loaded (via include).	Includes these view variables:</li>
	 * <ul>
	 *	<li>switch: The current Switch retrieved</li>
	 * </ul>
	 * </ul>
	 *
	 * @throws Exception If no such switch of the given public_uuid is found
	 * @return void
	 *
	 */
	public function view(): void {

		if (!isset($this->currentUser)) {
			$this->view->setLayout("header_logged_out");
		}

		if(isset($_GET["private_uuid"])){
			$this->view_private();
		}elseif(isset($_GET["public_uuid"])){
			$this->view_public();
		}else{
			throw new Exception("Either public_uuid or private_uuid is mandatory");
		}

	}

	/**
	* Action to view a given switch (with public uuid)
	*
	* This action should only be called by view()
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>public_uuid: Public UUID of the switch (via HTTP GET)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>switches/publicView: If switch is successfully loaded (via include).	Includes these view variables:</li>
	* <ul>
	*	<li>switch: The current Switch retrieved</li>
	* </ul>
	* </ul>
	*
	* @throws Exception If no such switch of the given public_uuid is found
	* @return void
	*
	*/
	private function view_public(): void {

		$public_uuid = $_GET["public_uuid"];

		// find the Switch object in the database
		$switch = $this->switchMapper->findByPublicUUID($public_uuid);

		if ($switch == NULL) {
			throw new Exception("no such switch with public_uuid: ".$public_uuid);
		}

		// put the Switch object to the view
		$this->view->setVariable("switch", $switch);
		$this->view->setVariable("hasPermissions", false);
		$this->view->setVariable("numSubscriptions", $this->switchMapper->getNumSubscriptions($switch->getPublicUuid()));
		if(isset($this->currentUser)){
			$isSubscribed = $this->switchMapper->isSubscribed($this->currentUser->getUsername(), $switch);
		}else{
			$isSubscribed = false;
		}
		$this->view->setVariable("isSubscribed", $isSubscribed);

		// render the view (/view/switches/view.php)
		$this->view->render("switches", "view");

	}

	/**
	 * Action to view a given switch (with private uuid)
	 *
	 * This action should only be called by view()
	 *
	 * The expected HTTP parameters are:
	 * <ul>
	 * <li>private_uuid: Private UUID of the switch (via HTTP GET)</li>
	 * </ul>
	 *
	 * The views are:
	 * <ul>
	 * <li>switches/privateView: If switch is successfully loaded (via include).	Includes these view variables:</li>
	 * <ul>
	 *	<li>switch: The current Switch retrieved</li>
	 * </ul>
	 * </ul>
	 *
	 * @throws Exception If no such switch of the given private_uuid is found
	 * @return void
	 *
	 */
	private function view_private(): void {

		$private_uuid = $_GET["private_uuid"];

		// find the Switches object in the database
		$switch = $this->switchMapper->findByPrivateUUID($private_uuid);

		if ($switch == NULL) {
			throw new Exception("no such switch with private_uuid: ".$private_uuid);
		}

		// put the Switch object to the view
		$this->view->setVariable("switch", $switch);
		$this->view->setVariable("hasPermissions", true);
		$this->view->setVariable("numSubscriptions", $this->switchMapper->getNumSubscriptions($switch->getPublicUuid()));
		if(isset($this->currentUser)){
			$isSubscribed = $this->switchMapper->isSubscribed($this->currentUser->getUsername(), $switch);
		}else{
			$isSubscribed = false;
		}
		$this->view->setVariable("isSuscribed", $isSubscribed);


		// render the view (/view/switches/view.php)
		$this->view->render("switches", "view");

	}

	/**
	 * Action to switch on or off a switch
	 *
	 * This function should only be called via POST
	 * It modifies the switch in the database with
	 * new power_off and last_power_on dates.
	 *
	 * The expected HTTP parameters are:
	 * <ul>
	 * <li>public_uuid: Public uuid of the switch (via HTTP POST)</li>
	 * <li>private_uuid: Private uuid of the switch (via HTTP POST)</li>
	 * <li>switch_state: Current state of switch (via HTTP POST)</li>
	 * <li>time_on: Time until power off (via HTTP POST)</li>
	 * </ul>
	 *
	 * The views are:
	 * <ul>
	 * <li>switches/dashboard: If switch was successfully switched on or off (via redirect)</li>
	 * <ul>
	 *	<li>switch: The current Switch instance, empty or being added (but not validated)</li>
	 *	<li>errors: Array including per-field validation errors</li>
	 * </ul>
	 * </ul>
	 * @return void
	 * @throws Exception if no user is in session
	 * @throws Exception if there is not any switch with the provided public_uuid
	 * @throws Exception if the current logged user is not the author of the switch
	 * @throws Exception if no public_uuid was provided
	 */
	public function changeSwitchState(): void {
		if (!isset($_REQUEST["public_uuid"]) && !isset($_REQUEST["private_uuid"])) {
			throw new Exception("A public switch uuid or private switch uuid is mandatory");
		}

		if(isset($_POST["public_uuid"]) && !isset($_POST["private_uuid"])){
			if (!isset($this->currentUser)) {
				throw new Exception("Not in session. Switching on/off switches requires login");
			}

			// Get the Switch object from the database
			$public_uuid = $_POST["public_uuid"];
			$switch = $this->switchMapper->findByPublicUUID($public_uuid);

			// Does the switch exist?
			if ($switch == NULL) {
				throw new Exception("no such switch with public uuid: ".$public_uuid);
			}

			// Check if the Switch owner is the currentUser (in Session)
			if ($switch->getOwner()->getUsername() != $this->currentUser->getUsername()) {
				throw new Exception("logged user is not the author of the switch uuid ".$public_uuid);
			}

		} elseif (isset($_POST["private_uuid"])) {

			// Get the Switch object from the database
			$private_uuid = $_POST["private_uuid"];
			$switch = $this->switchMapper->findByPrivateUUID($private_uuid);

			// Does the switch exist?
			if ($switch == NULL) {
				throw new Exception("no such switch with private uuid: ".$private_uuid);
			}
		}else{
			// Se accede por HTTP GET --> redirect a dashboard
			$this->view->setVariable("errors", "solo se permite acceder a esta funcionalidad desde POST"); //DEBUG
			$this->view->redirect("switches", "index");
			die();
		}

		// populate the Switch object with data form the form
		$switchPowerOffDate = new DateTime();
		if(!isset($_REQUEST["switch_state"]) && filter_var($_POST["time_on"], FILTER_VALIDATE_INT) !== false){
			$tempSwitchedOn = intval($_POST["time_on"]);
			if ($tempSwitchedOn > 120){
				$tempSwitchedOn = 120;
			}elseif ($tempSwitchedOn <= 0){
				throw new Exception("switch on time must be upper than zero. Value received".$tempSwitchedOn);
			}
			$switchPowerOffDate->modify("+".$tempSwitchedOn." minutes");
			$switch->setPowerOff($switchPowerOffDate);
			$switch->setLastPowerOn(new DateTime());

			// Enviar email a los suscriptores
			$suscribers = $this->switchMapper->phpEmail($switch->getPublicUuid());
			$nombre_switch = $switch->getSwitchName();
			$user_switch_name = $switch->getOwner()->getUsername();
			foreach ($suscribers as $suscriber){
				$this->enviarCorreo($suscriber->getEmail(), $suscriber->getUsername(), $nombre_switch, $user_switch_name);
			}
		}else{
			$switch->setPowerOff($switchPowerOffDate);
		}

		try {
			// validate Switch object
			$switch->checkIsValidForUpdate(); // if it fails, ValidationException

			// update the Switch object in the database
			$this->switchMapper->update($switch);

			// POST-REDIRECT-GET
			// Everything OK, we will redirect the user to the list of switches
			// We want to see a message after redirection, so we establish
			// a "flash" message (which is simply a Session variable) to be
			// get in the view after redirection.
			$this->view->setFlash(sprintf(i18n("Switch \"%s\" successfully powered \"%s\"."),
				$switch ->getSwitchName(), (isset($_REQUEST["switch_state"])?"off":"on")));

			// perform the redirection. More or less:
			// header("Location: index.php?controller=switches&action=dashboard")
			// die();
			header("Location: ".$_SERVER["HTTP_REFERER"]);
			die();

		}catch(ValidationException $ex) {
			// Get the errors array inside the exepction...
			$errors = $ex->getErrors();

			// And put it to the view as "errors" variable
			$this->view->setVariable("errors", $errors);
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


/**
 * Action to subscribe to a given switch
 * 
 * This action should only be called via POST
 * 
 * The expected HTTP parameters are:
 * <ul>
 * <li>public_uuid: Public uuid of the switch (via HTTP POST)</li>
 * <li>user_name: Username of the user to subscribe (via HTTP POST)</li>
 * </ul>
 */
public function subscribe(): void {

	if(!isset($_POST["public_uuid"])){
		throw new Exception("public_uuid is mandatory");
	}
	if(!isset($_POST["user_name"])){
		throw new Exception("user_name is mandatory");
	}

	$public_uuid = $_POST["public_uuid"];

	// Check if the Switch suscriber is the currentUser (in Session)
	if ($_POST["user_name"] != $this->currentUser->getUsername()) {
		throw new Exception("logged user is not the author of the switch uuid ".$public_uuid);
	}

	$switch = $this->switchMapper->findByPublicUUID($public_uuid);

	// Does the switch exist?
	if ($switch == NULL) {
		throw new Exception("no such switch with public uuid: ".$public_uuid);
	}

	if($this->switchMapper->suscribeToSwitch($this->currentUser->getUsername(), $switch) != 0){
		$this->view->setFlash(sprintf(i18n("Switch \"%s\" successfully subscribed."), $switch->getSwitchName()));
	}else{
		$errors = array();
		$errors["general"] = sprintf(i18n("Error occurred when subscribing to switch \"%s\""), $switch->getSwitchName());
		$this->view->setVariable("errors", $errors);
	}

	// Se redirige a la página anterior
	header("Location: ".$_SERVER["HTTP_REFERER"]);
	die();
	
}

/**
 * Action to unsubscribe to a given switch
 * 
 * This action should only be called via POST
 * 
 * The expected HTTP parameters are:
 * <ul>
 * <li>public_uuid: Public uuid of the switch (via HTTP POST)</li>
 * <li>user_name: Username of the user to subscribe (via HTTP POST)</li>
 * </ul>
 */
public function unsubscribe(): void {
	if(!isset($_POST["public_uuid"])){
		throw new Exception("public_uuid is mandatory");
	}
	if(!isset($_POST["user_name"])){
		throw new Exception("user_name is mandatory");
	}

	$public_uuid = $_POST["public_uuid"];

	// Check if the Switch suscriber is the currentUser (in Session)
	if ($_POST["user_name"] != $this->currentUser->getUsername()) {
		throw new Exception("logged user is not the author of the switch uuid ".$public_uuid);
	}

	$switch = $this->switchMapper->findByPublicUUID($public_uuid);
	
	// Does the switch exist?
	if ($switch == NULL) {
		throw new Exception("no such switch with public uuid: ".$public_uuid);
	}
	if($this->switchMapper->removeSuscriptionToSwitch($this->currentUser->getUsername() ,$switch) != 0){
		$this->view->setFlash(sprintf(i18n("Switch subscription \"%s\" successfully deleted."), $switch->getSwitchName()));
	}else{
		$errors = array();
		$errors["general"] = sprintf(i18n("Error occurred when deleting a subscription to switch \"%s\""), $switch->getSwitchName());
		$this->view->setVariable("errors", $errors);
	}

	// Se redirige a la página anterior
	header("Location: ".$_SERVER["HTTP_REFERER"]);
	die();
}

	/**
	* Action to add a new switch
	*
	* When called via GET, it shows the add form
	* When called via POST, it adds the switch to the
	* database
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>switch_name: Name of the switch (via HTTP POST)</li>
	* <li>description: Description of the switch (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>switches/add: If this action is reached via HTTP GET (via include)</li>
	* <li>switches/dashboard: If switch was successfully added (via redirect)</li>
	* <li>switches/add: If validation fails (via include). Includes these view variables:</li>
	* <ul>
	*	<li>switch: The current MySwitch instance, empty or
	*	being added (but not validated)</li>
	*	<li>errors: Array including per-field validation errors</li>
	* </ul>
	* </ul>
	* @throws Exception if no user is in session
	* @return void
	*/
	public function add() {
		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Adding switches requires login");
		}

		$switch = new MySwitch("",$this->currentUser);

		if(isset($_POST["cancel"])){ // reaching via HTTP Post...

			$this->view->redirect("switches", "index");

		}else if (isset($_POST["create"])) { // reaching via HTTP Post...

			// populate the Switch object with data from the form
			$switch->setSwitchName($_POST["switch_name"]);
			$switch->setDescription($_POST["switch_description"]);

			// The user of the Switch is the currentUser (user in session)
			$switch->setOwner($this->currentUser);

			try {
				// validate Switch object
				$switch->checkIsValidForCreate(); // if it fails, ValidationException

				// save the Switch object into the database
				$this->switchMapper->save($switch);

				// POST-REDIRECT-GET
				// Everything OK, we will redirect the user to the list of switches
				// We want to see a message after redirection, so we establish
				// a "flash" message (which is simply a Session variable) to be
				// get in the view after redirection.
				$this->view->setFlash(sprintf(i18n("Switch \"%s\" successfully added."),$switch ->getSwitchName()));

				// perform the redirection. More or less:
				// header("Location: index.php?controller=switches&action=index")
				// die();
				$this->view->redirect("switches", "index");

			}catch(ValidationException $ex) {
				// Get the errors array inside the exception...
				$errors = $ex->getErrors();
				// And put it to the view as "errors" variable
				$this->view->setVariable("errors", $errors);
			}
		}

		// Put the Switch object visible to the view
		$this->view->setVariable("switch", $switch);

		// render the view (/view/switches/add.php)
		$this->view->render("switches", "add");

	}

	/**
	 * Action to edit a switch
	 *
	 * When called via GET, it shows an edit form
	 * including the current data of the Switch.
	 * When called via POST, it redirects the user (cancel)
	 * modifies (edit) or delete (delete) the switch in the
	 * database.
	 *
	 * The expected HTTP parameters are:
	 * <ul>
	 * <li>public_uuid: Public uuid of the switch (edit or delete via HTTP POST and GET)</li>
	 * <li>reset_private_uuid: If user want to reset the private uuid of the switch (edit via HTTP POST)</li>
	 * <li>switch_name: Name of the switch (edit via HTTP POST)</li>
	 * <li>description: Description of the switch (edit via HTTP POST)</li>
	 * <li>edit/delete/cancel: The action to perform </li>
	 * </ul>
	 *
	 * The views are:
	 * <ul>
	 * <li>switches/edit: If this action is reached via HTTP GET (via include)</li>
	 * <li>switches/dashboard: If switch was successfully edited or if action is cancel (via redirect)</li>
	 * <li>switches/edit: If validation fails (via include). Includes these view variables:</li>
	 * <ul>
	 *	<li>switch: The current Switch instance, empty or being added (but not validated)</li>
	 *	<li>errors: Array including per-field validation errors</li>
	 * </ul>
	 * </ul>
	 * @return void
	 *@throws Exception if no user is in session
	 * @throws Exception if there is not any switch with the provided public_uuid
	 * @throws Exception if the current logged user is not the author of the switch
	 * @throws Exception if no public_uuid was provided
	 */
	public function edit(): void {

		if (!isset($_REQUEST["public_uuid"])) {
			throw new Exception("A switch uuid is mandatory");
		}

		if (!isset($this->currentUser)) {
			throw new Exception("Not in session. Editing switches requires login");
		}

		// Get the Switch object from the database
		$public_uuid = $_REQUEST["public_uuid"];
		$switch = $this->switchMapper->findByPublicUUID($public_uuid);

		// Does the switch exist?
		if ($switch == NULL) {
			throw new Exception("no such switch with id: ".$public_uuid);
		}

		// Check if the Switch owner is the currentUser (in Session)
		if ($switch->getOwner()->getUsername() != $this->currentUser->getUsername()) {
			throw new Exception("logged user is not the author of the switch uuid ".$public_uuid);
		}

		if (isset($_POST["modify"])) { // reaching via HTTP Post...
			// Go to modify
			$this->modify();
		}else if(isset($_POST["delete"])) { // reaching via HTTP Post...
			// Go to delete
			$this->delete();
		}else if(isset($_POST["cancel"])){ // reaching via HTTP Post...
			// render the view (/view/switches/index.php)
			$this->view->redirect("switches", "index");
		}else{ // other via HTTP Post or vía HTTP Get...

			// Put the Switch object visible to the view
			$this->view->setVariable("switch", $switch);

			// render the view (/view/switches/edit.php)
			$this->view->render("switches", "edit");
		}
	}

	/**
	* Action to modify a switch
	*
	* This action should only be called by edit()
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>public_uuid: Public uuid of the switch (via HTTP POST)</li>
	* <li>reset_private_uuid: If user want to reset the private uuid of the switch (via HTTP POST)</li>
	* <li>switch_name: Name of the switch (via HTTP POST)</li>
	* <li>description: Description of the switch (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>switches/dashboard: If switch was successfully edited (via redirect)</li>
	* <li>switches/edit: If validation fails (via include). Includes these view variables:</li>
	* <ul>
	*	<li>switch: The current Switch instance, empty or being added (but not validated)</li>
	*	<li>errors: Array including per-field validation errors</li>
	* </ul>
	* </ul>
	* @return void
	* @throws Exception if there is not any switch with the provided public_uuid
	*/
	private function modify(): void {

		// Get the Switch object from the database
		$public_uuid = $_REQUEST["public_uuid"];
		$switch = $this->switchMapper->findByPublicUUID($public_uuid);

		// reaching via HTTP Post...

		// populate the Switch object with data form the form
		$switch->setSwitchName($_POST["switch_name"]);
		$switch->setDescription(trim($_POST["switch_description"]));

		if(isset($_POST["reset_private_uuid"])){
			$switch->setPrivateUuid($this->switchMapper->generateUUID());
		}

		try {
			// validate Switch object
			$switch->checkIsValidForUpdate(); // if it fails, ValidationException

			// update the Switch object in the database
			$this->switchMapper->update($switch);

			// POST-REDIRECT-GET
			// Everything OK, we will redirect the user to the list of switches
			// We want to see a message after redirection, so we establish
			// a "flash" message (which is simply a Session variable) to be
			// get in the view after redirection.
			$this->view->setFlash(sprintf(i18n("Switch \"%s\" successfully updated."),$switch ->getSwitchName()));

			// perform the redirection. More or less:
			// header("Location: index.php?controller=switches&action=index")
			// die();
			$this->view->redirect("switches", "index");

		}catch(ValidationException $ex) {
			// Get the errors array inside the exepction...
			$errors = $ex->getErrors();
			// And put it to the view as "errors" variable
			$this->view->setVariable("errors", $errors);
		}
	}

	/**
	* Action to delete a switch
	*
	* This action should only be called by edit()
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>id: public_uuid of the switch (via HTTP POST)</li>
	* </ul>
	*
	* The views are:
	* <ul>
	* <li>switches/dashboard: If switch was successfully deleted (via redirect)</li>
	* </ul>
	* @throws Exception if no public_uuid was provided
	* @throws Exception if no user is in session
	* @throws Exception if there is not any switch with the provided public_uuid
	* @throws Exception if the author of the switch to be deleted is not the current user
	* @return void
	*/
	private function delete(): void {

		// Get the Switch object from the database
		$public_uuid = $_REQUEST["public_uuid"];
		$switch = $this->switchMapper->findByPublicUUID($public_uuid);

		// Delete the Switch object from the database
		$this->switchMapper->delete($switch);

		// POST-REDIRECT-GET
		// Everything OK, we will redirect the user to the list of switches
		// We want to see a message after redirection, so we establish
		// a "flash" message (which is simply a Session variable) to be
		// get in the view after redirection.
		$this->view->setFlash(sprintf(i18n("Switch \"%s\" successfully deleted."),$switch->getSwitchName()));

		// perform the redirection. More or less:
		// header("Location: index.php?controller=switches&action=index")
		// die();
		$this->view->redirect("switches", "index");

	}
}