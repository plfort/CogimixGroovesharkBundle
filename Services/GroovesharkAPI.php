<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;


class GroovesharkAPI extends \gsAPI{

    public function __construct($key,$secret){

        parent::__construct($key,$secret);
    }

}