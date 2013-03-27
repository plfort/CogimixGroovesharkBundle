<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use Cogipix\CogimixGroovesharkBundle\lib\Grooveshark\gsAPI;

class GroovesharkAPI extends gsAPI{

    public function __construct($key,$secret){

        parent::__construct($key,$secret);
    }

}