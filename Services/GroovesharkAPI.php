<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use plfort\GroovesharkAPI\GroovesharkAPI as BaseGroovesharkAPI;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class GroovesharkAPI extends BaseGroovesharkAPI
{

    protected $session;

    public function __construct($key, $secret, SessionInterface $session)
    {

        $this->session = $session;
        $gsSessionId = null;
        if ($session->get('gsSession')) {
            $gsSessionId = $session->get('gsSession');
        }
        $country = null;
        if ($session->get('gsCountry')) {
            $country = $session->get('gsCountry');
        }
        
        parent::__construct($key, $secret, $gsSessionId, $country);
    }

    public function getStreamKeyStreamServer($songID, $lowBitrate = false)
    {
        if (empty($this->country)) {
            $this->session->set('gsCountry', $this->getCountry());
        }
        return parent::getStreamKeyStreamServer($songID, $lowBitrate);
    }

    public function getSongSearchResults($query, $country = null, $limit = null, $page = null)
    {
        if (empty($this->country)) {
            $this->session->set('gsCountry', $this->getCountry());
        }
        return parent::getSongSearchResults($query, $country, $limit, $page);
    }

    protected function makeCall($method, $args = array(), $resultKey = null, $https = false)
    {
        
        if ($method != 'startSession' && empty($this->sessionID)) {
            
            $this->session->set('gsSession', $this->startSession());
        }
        
        return parent::makeCall($method, $args, $resultKey, $https);
    }
}