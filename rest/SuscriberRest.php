<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");
require_once(__DIR__."/../model/Switch.php");
require_once(__DIR__."/../model/SwitchMapper.php");
require_once(__DIR__."/BaseRest.php");

class SuscriberRest extends BaseRest {
	private $userMapper;
	private $user;
    private $switchMapper;

	public function __construct() {
		parent::__construct();

		$this->user = new User();
		$this->userMapper = new UserMapper();
        $this->switchMapper = new SwitchMapper();
	}

	public function suscripcionSwitch($public_uuid, $is_suscribed):void{
		try{
			if($this->switchMapper->findByPublicUUID($public_uuid) == NULL){
				parent::error404("Switch no encontrado");
			}else{
				$switch = $this->switchMapper->findByPublicUUID($public_uuid);
				$username = $this->user->getUsername();
				if($is_suscribed){
					$this->switchMapper->removeSuscriptionToSwitch($username, $switch);
					parent::answerString200("Desuscrito del switch");
				}
				else{
					$this->switchMapper->suscribeToSwitch($username, $switch);
					parent::answerString200("Suscrito al switch");
				
				}
		

			}
		}catch(Exception $e){
			parent::error500();
		}


	}


	public function getSuscribers(){
		$username = $this->user->getUsername();
		
		$url = $_SERVER['REQUEST_URI'];
    	$parts = explode('/', $url);
    	$public_uuid = $parts[count($parts) - 3];
		try{
			if($this->switchMapper->findByPublicUUID($public_uuid) == NULL){
				parent::error404("Switch no encontrado");
				
			}
			$switch = $this->switchMapper->findByPublicUUID($public_uuid);
			$is_suscribed = $this->switchMapper->isSubscribed($username, $switch);
			parent::answerJson200(array( "public_uuid"=>$public_uuid, "is_suscribed" => $is_suscribed));
			

		}catch(Exception $e){
			parent::error500();
		}
	}
}
$suscriberRest = new SuscriberRest();
URIDispatcher::getInstance()
->map("PUT", "/switch/subscriber", array($suscriberRest,"suscripcionSwitch"))
->map("GET", "/switch/$1/suscriber/$2", array($suscriberRest,"isUserSubscribed"));