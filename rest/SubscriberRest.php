<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");
require_once(__DIR__."/../model/Switch.php");
require_once(__DIR__."/../model/SwitchMapper.php");
require_once(__DIR__."/BaseRest.php");

class SubscriberRest extends BaseRest
{
    private $userMapper;
    private $user;
    private $switchMapper;

    public function __construct()
    {
        parent::__construct();

        $this->user = new User();
        $this->userMapper = new UserMapper();
        $this->switchMapper = new SwitchMapper();
    }

    public function modifySubscription($data): void
    {
        $currentUser = parent::authenticateUser();
        try {
            $switch = $this->switchMapper->findByPublicUUID($data->switch_public_uuid);
            if ($switch == NULL) {
                parent::error404("Switch no encontrado");
            } else {
                $username = $currentUser->getUsername();
                if ($data->is_suscribed) {
                    $this->switchMapper->removeSuscriptionToSwitch($username, $switch);
                    parent::answerString200("Desuscrito del switch");
                } else {
                    $this->switchMapper->suscribeToSwitch($username, $switch);
                    parent::answerString200("Suscrito al switch");
                }
            }
        } catch (Exception $e) {
            parent::error500();
        }


    }


    public function isSubscribed($public_uuid, $userName)
    {
        $user_name = parent::authenticateUser();
        if ($user_name != $userName){
            parent::error403("User in URL does not match JWT's user");
        }
        try {
            if ($this->switchMapper->findByPublicUUID($public_uuid) == NULL) {
                parent::error404("Switch no encontrado");
            }
            $is_suscribed = $this->switchMapper->isSubscribed($user_name->getUsername(), $public_uuid);
            parent::answerJson200(array("public_uuid" => $public_uuid, "is_subscribed" => $is_suscribed));

        } catch (Exception $e) {
            parent::error500();
        }
    }

    public function getNumSuscribers($public_uuid)
    {
        try {
            if ($this->switchMapper->findByPublicUUID($public_uuid) == NULL) {
                parent::error404("Switch no encontrado");
            } else {
                $this->switchMapper->getNumSubscriptions($public_uuid);
                $numSubscriptions = $this->switchMapper->getNumSubscriptions($public_uuid);
                parent::answerJson200(array("num_subscriptions" => $numSubscriptions));
            }
        }catch (Exception){
            parent::error500();
        }
    }
    }

$suscriberRest = new SubscriberRest();
URIDispatcher::getInstance()
->map("PUT", "/switch/subscriber", array($suscriberRest, "modifySubscription"))
->map("GET", "/switch/$1/subscriber/$2", array($suscriberRest,"isSubscribed")) // $1: public_uuid, $2: user_name
->map("GET", "/switch/$1/numSubscribers", array($suscriberRest,"getNumSuscribers"));
