<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use plfort\GroovesharkAPI\GroovesharkAPI as BaseGroovesharkAPI;

class GroovesharkAPI extends BaseGroovesharkAPI{


    /*
     * Set the current session for use with methods
    */
    public function setSession($sessionID)
    {
        $this->sessionID = $sessionID;
    }

    public function getPopularSongs(){

        return $this->getPopularSongsToday();
    }

}