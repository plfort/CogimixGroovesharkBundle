<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

class GroovesharkAPIFactory{

    private $key;
    private $secret;
    private $apiClass;
    private $container;

    private $gsApi=null;

    public function __construct($key,$secret,$apiClass,ContainerInterface $container){
        $this->key = $key;
        $this->secret= $secret;
        $this->apiClass=$apiClass;
        $this->container=$container;
    }

    public function getGroovesharkAPI(){
        if($this->gsApi === null){
            $this->gsApi= new $this->apiClass($this->key,$this->secret);
            $session=$this->container->get('session');
            if ($session->get('gsSession',false)) {
                $this->gsApi->setSession($session->get('gsSession'));
            } else {
                $session->set('gsSession',$this->gsApi->startSession());
            }
            if ($session->get('gsCountry',false)) {
                $this->gsApi->setCountry($session->get('gsCountry',false));
            } else {
                $session->set('gsCountry',$this->gsApi->getCountry());

            }
        }
       return $this->gsApi;
    }
}