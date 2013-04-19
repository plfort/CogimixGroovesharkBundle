<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use Cogipix\CogimixCommonBundle\Entity\TrackResult;
use Cogipix\CogimixCommonBundle\MusicSearch\AbstractMusicSearch;

class GroovesharkSongMusicSearch extends AbstractMusicSearch{

    private $gsSearch;
    private $resultBuilder;

    public function __construct($gsApi,$resultBuilder){
        $this->gsSearch=new \gsSearch($gsApi);
        $this->resultBuilder=$resultBuilder;
    }

    protected function parseResponse($results){
        return $this->resultBuilder->createArrayFromGroovesharkTracks($results);
    }

    protected function buildQuery(){
        $this->gsSearch->setTitle($this->searchQuery->getSongQuery());
        $this->gsSearch->setArtist($this->searchQuery->getArtistQuery());
    }

    protected function executeQuery(){
        $this->logger->info('Groovshark executeQuery');
        $results= $this->gsSearch->songSearchResults();
        if($results){
            return $this->parseResponse($results);
        }else{
            $this->logger->err(\gsSearch::$lastError);
        }
    }

    public function  getName(){
        return 'Grooveshark';
    }

    public function  getAlias(){
        return 'gsservice';
    }

    public function getResultTag(){
        return 'gs';
    }

    public function getDefaultIcon(){
        return 'bundles/cogimixgrooveshark/images/gs-icon.png';
    }


}

?>