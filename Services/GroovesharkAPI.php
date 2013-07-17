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

        return $this->getPopularSongsToday();
    }

}