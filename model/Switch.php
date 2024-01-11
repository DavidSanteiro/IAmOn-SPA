<?php
// file: model/Switch.php

require_once(__DIR__."/../core/ValidationException.php");

/**
* Class Switch
*
* Represents a Switch in IAmOn. A Switch was created by an
* specific User
*
* Nota: Por restricciones de PHP no puede tener el nombre Switch
*
*/
class MySwitch {

	const REGEX_SWITCH_NAME = "/^.{1,100}$";
	const REGEX_SWITCH_DESCRIPTION = "/^.{0,400}$";

	/**
	* The public uuid of this switch
	* @var string
	*/
	private $public_uuid;

	/**
	 * The private uuid of this switch
	 * @var string
	 */
	private $private_uuid;

	/**
	* The name of this switch
	* @var string
	*/
	private $switch_name;

	/**
	* The description of this switch
	* @var string
	*/
	private $description;

	/**
	* The owner of this switch
	* @var User
	*/
	private $owner;

	/**
	 * The last power on date (if never switched on -> null)
	 * @var DateTime
	 */
	private $last_power_on;


	/**
	 * The last power off date or the date switch will power off
	 * @var DateTime
	 */
	private $power_off;

	/**
	 * The constructor with all parameters
	 * @param string $public_uuid The public uuid of this switch
	 * @param string $private_uuid The private uuid of this switch
	 * @param string $switch_name The name of this switch
	 * @param string $description The description of this switch
	 * @param User $owner The owner of this switch
	 * @param DateTime $power_off The last power off date or the date switch will power off
	 * @param DateTime $last_power_on The last power on date (if never switched on -> null)
	 */
	public function __construct(string $switch_name,User $owner, string $public_uuid=NULL, string $private_uuid=NULL,
															string $description=NULL, DateTime $power_off=NULL, DateTime $last_power_on=NULL)
	{
		//Compulsory:
		$this->switch_name = ($switch_name==NULL)?NULL:trim($switch_name);
		$this->owner = $owner;
		//Voluntary:
		$this->public_uuid = ($public_uuid==NULL)?NULL:trim($public_uuid);
		$this->private_uuid = ($private_uuid==NULL)?NULL:trim($private_uuid);
		$this->description = ($description==NULL)?"":trim($description);
		$this->power_off = $power_off;
		$this->last_power_on = $last_power_on;
	}


	/**
	* Gets the public uuid of this post
	*
	* @return string The id of this post
	*/
	public function getPublicUuid(): string {
		return $this->public_uuid;
	}

	/**
	 * Gets the private uuid of this post
	 *
	 * @return string The id of this post
	 */
	public function getPrivateUuid(): string {
		return $this->private_uuid;
	}

	/**
	 * Sets the private uuid of this switch
	 *
	 * @param string $private_uuid the private uuid of this switch
	 * @return void
	 */
	public function setPrivateUuid(string $private_uuid){
		$this->private_uuid = ($private_uuid==NULL)?NULL:trim($private_uuid);
	}

	/**
	* Gets the name of this switch
	*
	* @return string The name of this switch
	*/
	public function getSwitchName(): string {
		return $this->switch_name;
	}

	/**
	* Sets the name of this switch
	*
	* @param string $switch_name the name of this switch
	* @return void
	*/
	public function setSwitchName(string $switch_name) {
		$this->switch_name = ($switch_name==NULL)?NULL:trim($switch_name);
	}

	/**
	* Gets the description of this switch
	*
	* @return string The description of this switch
	*/
	public function getDescription(): string {
		return $this->description;
	}

	/**
	* Sets the description of this switch
	*
	* @param string $description the description of this switch
	* @return void
	*/
	public function setDescription(string $description) {
		$this->description = ($description==NULL)?"":trim($description);
	}

	/**
	* Gets the owner of this switch
	*
	* @return User The owner of this switch
	*/
	public function getOwner(): User {
		return $this->owner;
	}

	/**
	* Sets the owner of this switch
	*
	* @param User $owner the owner of this switch
	* @return void
	*/
	public function setOwner(User $owner) {
		$this->owner = $owner;
	}

	/**
	 * Gets the last power off date or the date switch will power off
	 *
	 * @return DateTime The last power off date or the date switch will power off
	 */
	public function getPowerOff(): DateTime {
		return $this->power_off;
	}

	/**
	 * Sets the last power off date or the date switch will power off
	 *
	 * @param DateTime $power_off the last power off date or the date switch will power off
	 * @return void
	 */
	public function setPowerOff(DateTime $power_off) {
		$this->power_off = $power_off;
	}

	/**
	 * Gets the last power on date (if never switched on -> null)
	 *
	 * @return DateTime The last power on date (if never switched on -> null)
	 */
	public function getLastPowerOn() {
		return $this->last_power_on;
	}

	/**
	 * Sets the last power on date (if never switched on -> null)
	 *
	 * @param DateTime $last_power_on the last power on date (if never switched on -> null)
	 * @return void
	 */
	public function setLastPowerOn(DateTime $last_power_on) {
		$this->last_power_on = $last_power_on;
	}

	/**
	* Checks if the current instance is valid
	* for being created in the database.
	*
	* @throws ValidationException if the instance is
	* not valid
	*
	* @return void
	*/
	public function checkIsValidForCreate() {
		$errors = array();
		// Public uuid (se genera en la base de datos)
//		if (isset($this->public_uuid)) { //se genera en la base de datos pero en una consulta previa -> no funciona lastInsertedId()
//			$errors["switch_public_uuid"] = "unable to create switch that has been already created (it has assigned an public uuid)";
//		}
		// Private uuid (se genera en la base de datos)
		if (isset($this->private_uuid)) {
			$errors["switch_private_uuid"] = "unable to create switch that has been already created (it has assigned an private uuid)";
		}
		// Owner
		if (!isset($this->owner)) {
			$errors["user_name"] = "all switch must have an owner";
		}
		// Switch name
		if (!isset($this->switch_name) || strlen($this->switch_name) == 0) {
			$errors["switch_name"] = "switch name is mandatory";
		}
		if(strlen($this->switch_name) > 100){
			$errors["switch_name"] = "switch name must be shorter or equal to 100 characters";
		}
		// Description
		if (isset($this->description) && strlen($this->description) > 400 ) {
			$errors["switch_description"] = "description must be shorter or equal to 400 characters";
		}
		// Last power on
		if (isset($this->last_power_on)){
			$errors["switch_last_power_on"] = "it is not allowed to create switched on switches";
		}
		// Power Off

		if (sizeof($errors) > 0){
			throw new ValidationException($errors, "post is not valid for create");
		}
	}

	/**
	* Checks if the current instance is valid
	* for being updated in the database.
	*
	* @throws ValidationException if the instance is
	* not valid
	*
	* @return void
	*/
	public function checkIsValidForUpdate() {
		$errors = array();

		// Public uuid
		if (!isset($this->public_uuid) || strlen($this->public_uuid) == 0) {
			$errors["switch_public_uuid"] = "public uuid is mandatory to update an existent switch";
		}
		if (!isset($this->public_uuid) && strlen($this->public_uuid) > 36) {
			$errors["switch_public_uuid"] = "public uuid must be shorter or equal to 36 characters";
		}
		// Private uuid
		if (!isset($this->private_uuid) || strlen($this->private_uuid) == 0) {
			$errors["switch_private_uuid"] = "private uuid is mandatory to update an existent switch";
		}
		if (isset($this->private_uuid) && strlen($this->private_uuid) > 36) {
			$errors["switch_private_uuid"] = "private uuid must be shorter or equal to 36 characters";
		}
		// Owner
		if (!isset($this->owner)) {
			$errors["user_name"] = "all switch must have an owner";
		}
		// Power off
		if (!isset($this->power_off)) {
			$errors["switch_power_off"] = "all switch must have an power off date";
		}
		// Switch name
		if (!isset($this->switch_name) || strlen($this->switch_name) == 0) {
			$errors["switch_name"] = "switch name is mandatory";
		}
		if(strlen($this->switch_name) > 100){
			$errors["switch_name"] = "switch name must be shorter or equal to 100 characters";
		}
		// Description
		if (isset($this->description) && strlen($this->description) > 400 ) {
			$errors["switch_description"] = "description must be shorter or equal to 400 characters";
		}
		// Last power on
		if (isset($this->last_power_on) && $this->last_power_on > new DateTime()){
			$errors["switch_last_power_on"] = "last_power_on can't be a future date";
		}
		// Power Off
		if (!isset($this->last_power_on) && (!isset($this->power_off) || $this->power_off <= $this->last_power_on)){
			$errors["switch_last_power_on"] = "power_off must be greater than last_power_on";
		}


		if (sizeof($errors) > 0) {
			throw new ValidationException($errors, "switch is not valid for update");
		}
	}
	public function __toString() {
		$output = "Public UUID: " . $this->public_uuid . "\n";
		$output .= "Private UUID: " . $this->private_uuid . "\n";
		$output .= "Switch Name: " . $this->switch_name . "\n";
		$output .= "Description: " . $this->description . "\n";
		$output .= "Owner: " . $this->owner->getUsername() . "\n";
		$output .= "Power Off: " . $this->power_off->format('Y-m-d H:i:s') . "\n";
		$output .= "Power Off: " . $this->last_power_on->format('Y-m-d H:i:s') . "\n";

		return $output;
	}
}
