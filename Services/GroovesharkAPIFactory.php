<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

class GroovesharkAPIFactory{

    private $key;
    private $secret;
    private $apiClass;
    private $container;

    public function __construct($key,$secret,$apiClass,ContainerInterface $container){
        $this->key = $key;
        $this->secret= $secret;
        $this->apiClass=$apiClass;
        $this->container=$container;
    }

    public function getGroovesharkAPI(){

        $gsapi= new $this->apiClass($this->key,$this->secret);
        $session=$this->container->get('session');
        if ($session->get('gsSession',false)) {
            $gsapi->setSession($session->get('gsSession'));
        } else {
            $session->set('gsSession',$gsapi->startSession());
        }
        if ($session->get('gsCountry',false)) {
            $gsapi->setCountry($session->get('gsCountry',false));
        } else {
            $session->set('gsCountry',$gsapi->getCountry());

        }
       return $gsapi;
    }
}