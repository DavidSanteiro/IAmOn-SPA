<?php
// file: model/PostMapper.php
require_once(__DIR__."/../core/PDOConnection.php");

require_once(__DIR__."/../model/User.php");
require_once(__DIR__ . "/../model/Switch.php");
require_once ("Mail.php");


/**
* Class SwitchMapper
*
* Database interface for MySwitch entities
*
* @author lipido <lipido@gmail.com>
*/
class SwitchMapper {

	const DATE_FORMAT = "Y-m-d H:i:s";
	const REGEX_UUID = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i";

	/**
	* Reference to the PDO connection
	* @var PDO
	*/
	private $db;

	public function __construct() {
		$this->db = PDOConnection::getInstance();
	}

	/**
	* Retrieves all user switches
	*
	* @param User $user The user owner of the switches
	* @throws PDOException if a database error occurs
	* @return array Array of MySwitch instances with all information ($user contains the data of variable $user)
	*/
	public function findMySwitches(User $user): array {
		$stmt = $this->db->prepare(
			"SELECT public_uuid, private_uuid, switch_name, power_off, last_power_on, description
							FROM Switch WHERE user_name=?");
		$stmt->execute(array($user->getUsername()));
		$switches_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$userSwitches = array();

		foreach ($switches_db as $switch) {
			// Convertir la cadena de fecha y hora de MySQL en un objeto DateTime de PHP
			$power_off = DateTime::createFromFormat(self::DATE_FORMAT, $switch["power_off"], new DateTimeZone('UTC'));
			if(isset($switch["last_power_on"])){
				$last_power_on = DateTime::createFromFormat(self::DATE_FORMAT, $switch["last_power_on"], new DateTimeZone('UTC'));
				$last_power_on = $last_power_on->setTimezone(new DateTimeZone('Europe/Madrid'));
			} else{
				$last_power_on = null;
			}

			array_push($userSwitches, new MySwitch($switch["switch_name"], $user, $switch["public_uuid"],
				$switch["private_uuid"], $switch["description"], $power_off->setTimezone(new DateTimeZone('Europe/Madrid')), $last_power_on));
		}

		return $userSwitches;
	}

	/**
	 * Retrieves all user subscribed switches
	 *
	 * @param User $user The user subscribed to the switches
	 * @throws PDOException if a database error occurs
	 * @return array Array of MySwitch instances without private uuid from Switch,
	 * and without user password and user email from the Switch owner (User)
	 */
	public function findSuscribedSwitches(User $user) {
		$stmt = $this->db->prepare(
			"SELECT Switch.public_uuid, switch_name, last_power_on, power_off, description, Switch.user_name 
			FROM Switch, Suscriber WHERE Suscriber.public_uuid=Switch.public_uuid AND Suscriber.user_name=?");
		$stmt->execute(array($user->getUsername()));
		$switches_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$userSuscribedSwitches = array();

		foreach ($switches_db as $switch) {
			// Convertir la cadena de fecha y hora de MySQL en un objeto DateTime de PHP
			$power_off = DateTime::createFromFormat(self::DATE_FORMAT, $switch["power_off"], new DateTimeZone('UTC'));
			if(isset($switch["last_power_on"])){
				$last_power_on = DateTime::createFromFormat(self::DATE_FORMAT, $switch["last_power_on"], new DateTimeZone('UTC'));
				$last_power_on = $last_power_on->setTimezone(new DateTimeZone('Europe/Madrid'));
			} else{
				$last_power_on = null;
			}

			array_push($userSuscribedSwitches, new MySwitch($switch["switch_name"], new User($switch["user_name"]),
				$switch["public_uuid"], null, $switch["description"], $power_off->setTimezone(new DateTimeZone('Europe/Madrid')), $last_power_on));
		}

		return $userSuscribedSwitches;
	}

	/**
	 * Add a switch suscription to the database
	 *
	 * @param User $user The user subscribed to the switches
	 * @param MySwitch $switch The switch to be subscribed
	 * @throws PDOException if a database error occurs
	 * @return array Array of MySwitch instances without private uuid from Switch,
	 * and without user password and user email from the Switch owner (User)
	 */
	public function suscribeToSwitch(string $user_name, MySwitch $switch) : int {

		$switch_public_uuid = $switch->getPublicUuid();

		$stmt = $this->db->prepare(
			"INSERT INTO Suscriber (public_uuid, user_name) VALUES(?, ?);"
		);
		$stmt->execute(array($switch_public_uuid, $user_name));
		
		
		return $stmt->rowCount();
	}

	/**
	 * Delete a switch suscription from the database
	 *
	 * @param string $user_name The user subscribed to the switches
	 * @param MySwitch $switch The switch to be subscribed
	 * @throws PDOException if a database error occurs
	 * @return int number of affected rows,
	 */
	public function removeSuscriptionToSwitch (string $user_name, MySwitch $switch):int {

		$switch_public_uuid = $switch->getPublicUuid();

		$stmt = $this->db->prepare(
			"DELETE FROM Suscriber WHERE public_uuid=? AND user_name=? ;");
		$stmt->execute(array($switch_public_uuid, $user_name));



		return $stmt->rowCount();
	}

	/**
	 * Checks if a user is subscribed to a switch
	 *
	 * @param string $user_name The user subscribed to the switches
	 * @param MySwitch $public_uuid The switch to be subscribed
	 * @return bool if $user is subscribed to switch,
	 *@throws PDOException if a database error occurs
	 */
	public function isSubscribed(string $user_name, string $public_uuid) : bool
	{
		$stmt = $this->db->prepare("SELECT COUNT(*) FROM Suscriber 
                WHERE public_uuid=? AND user_name=?;");
		$stmt->execute(array($public_uuid, $user_name));
		return ($stmt->fetchColumn() == "1");
	}


	public function phpEmail($public_uuid): array {
		$stmt = $this->db->prepare("SELECT User.user_email, User.user_name FROM User, Suscriber WHERE User.user_name=Suscriber.user_name AND Suscriber.public_uuid=?");
		$stmt->execute(array($public_uuid));

		$suscribers = array();
		foreach ($stmt->fetchAll() as $suscriber){
			array_push($suscribers, new User($suscriber["user_name"], null, $suscriber["user_email"]));
		}
		return $suscribers;
	}


	public function getNumSubscriptions($public_uuid)
	{
		$stmt = $this->db->prepare("SELECT COUNT(*) FROM Suscriber WHERE Suscriber.public_uuid=?");
		$stmt->execute(array($public_uuid));

		return $stmt->fetchColumn();
	}


	/**
	 * Loads a Switch from the database given its public uuid
	 *
	 * Note: owner details (password and email) are not added to switch
	 *
	 * @return MySwitch The switch instance. NULL
	 * if the Post is not found
	 *@throws PDOException if a database error occurs
	 */
	public function findByPublicUUID($public_uuid){
		$stmt = $this->db->prepare("SELECT private_uuid, switch_name, power_off, last_power_on, description, user_name 
			FROM Switch WHERE public_uuid=?");
		$stmt->execute(array($public_uuid));
		$switch = $stmt->fetch(PDO::FETCH_ASSOC);

		if($switch != null) {

            $power_off = DateTime::createFromFormat(self::DATE_FORMAT, $switch["power_off"], new DateTimeZone('UTC'));
            if(isset($switch["last_power_on"])){
                $last_power_on = DateTime::createFromFormat(self::DATE_FORMAT, $switch["last_power_on"], new DateTimeZone('UTC'));
                $last_power_on = $last_power_on->setTimezone(new DateTimeZone('Europe/Madrid'));
            } else{
                $last_power_on = null;
            }

			return new MySwitch(
				$switch["switch_name"],
				new User($switch["user_name"]),
				$public_uuid,
				$switch["private_uuid"],
				$switch["description"],
				$power_off->setTimezone(new DateTimeZone('Europe/Madrid')),
				$last_power_on);
		} else {
			return NULL;
		}
	}

	/**
	 * Loads a Switch from the database given its private uuid
	 *
	 * Note: owner details (password and email) are not added to switch
	 *
	 * @return MySwitch The switch instance. NULL
	 * if the Post is not found
	 *@throws PDOException if a database error occurs
	 */
	public function findByPrivateUUID($private_uuid){
		$stmt = $this->db->prepare("SELECT public_uuid, switch_name, last_power_on, power_off, description, user_name 
			FROM Switch WHERE private_uuid=?");
		$stmt->execute(array($private_uuid));
		$switch = $stmt->fetch(PDO::FETCH_ASSOC);

		$fechaHora = DateTime::createFromFormat(self::DATE_FORMAT, $switch["power_off"], new DateTimeZone('UTC'));
		if(isset($switch["last_power_on"])){
			$last_power_on = DateTime::createFromFormat(self::DATE_FORMAT, $switch["last_power_on"], new DateTimeZone('UTC'));
			$last_power_on = $last_power_on->setTimezone(new DateTimeZone('Europe/Madrid'));
		} else{
			$last_power_on = null;
		}

		if($switch != null) {
			return new MySwitch(
				$switch["switch_name"],
				new User($switch["user_name"]),
				$switch["public_uuid"],
				$private_uuid,
				$switch["description"],
				$fechaHora->setTimezone(new DateTimeZone('Europe/Madrid')),
				$last_power_on);
		} else {
			return NULL;
		}
	}

	/**
	* Saves a switch into the database
	*
	* @param MySwitch $switch The switch to be saved
	* @return String The new switch public uuid
	* @throws PDOException if a database error occurs
	* @throws Exception if can't insert switch in database
	*/
	public function save(MySwitch $switch) : void {
		$stmt = $this->db->prepare("INSERT INTO Switch(public_uuid, user_name, switch_name, description) values (?,?,?,?)");
		$stmt->execute(array($switch->getPublicUuid(), $switch->getOwner()->getUsername(), $switch->getSwitchName(), $switch->getDescription()));
		if($stmt->rowCount() != 1){
			throw new Exception("The switch " . $switch->getPublicUuid()
				. " has not been inserted in the database (rowCount = " . $stmt->rowCount() . ")");
		}
	}

	/**
	* Updates a switch in the database
	*
	* @param MySwitch $switch The switch to be updated
	* @return void
	* @throws PDOException if a database error occurs
	* @throws Exception if can't update switch in database
	*/
	public function update(MySwitch $switch) : bool {
		$stmt = $this->db->prepare("UPDATE Switch SET user_name=?, switch_name=?, description=?, private_uuid=?,
				power_off=?, last_power_on=? WHERE public_uuid=?");
		$stmt->execute(array($switch->getOwner()->getUsername(), $switch->getSwitchName(), $switch->getDescription(),
			$switch->getPrivateUuid(), $switch->getPowerOff()->setTimezone(new DateTimeZone('UTC'))->format(self::DATE_FORMAT),
			$switch->getLastPowerOn()?->setTimezone(new DateTimeZone('UTC'))->format(self::DATE_FORMAT), $switch->getPublicUuid()));

		return ($stmt->rowCount() == 1);
	}

	public function generateUUID(){
		$stmt = $this->db->query("SELECT UUID() AS uuid");
		$uuid = $stmt->fetch(PDO::FETCH_ASSOC);
		return $uuid["uuid"];
	}




	/**
	* Deletes a switch into the database
	*
	* @param MySwitch $switch The switch to be deleted
	* @return void
	* @throws PDOException if a database error occurs
	* @throws Exception if can't update switch in database
	*/
	public function delete(MySwitch $switch) : void {
		$stmt = $this->db->prepare("DELETE FROM Switch WHERE public_uuid=?");
		$stmt->execute(array($switch->getPublicUuid()));

		if($stmt->rowCount() != 1){
			throw new Exception("The switch " . $switch->getPublicUuid()
				. " has not been deleted from the database (rowCount = " . $stmt->rowCount() . ")");
		}
	}
}
