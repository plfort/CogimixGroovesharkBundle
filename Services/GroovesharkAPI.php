<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;


class GroovesharkAPI extends \gsAPI{


    /*
     * Set the current session for use with methods
    */
    public function setSession($sessionID)
    {
        $this->sessionID = $sessionID;
    }

    public function getPopularSongs(){
       $return= $this->apiCall("getPopularSongsToday",array(),true);
       if (isset($return['decoded']['result']['songs'])) {
           return $return['decoded']['result']['songs'];
       } else {
           gsAPI::$lastError = $return['raw'];
           return false;
       }
    }
}