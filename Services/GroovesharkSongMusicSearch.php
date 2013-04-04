<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use Cogipix\CogimixCommonBundle\Entity\TrackResult;
use Cogipix\CogimixCommonBundle\MusicSearch\AbstractMusicSearch;

class GroovesharkSongMusicSearch extends AbstractMusicSearch{

    private $gsSearch;

    public function __construct($gsApi){
        $this->gsSearch=new \gsSearch($gsApi);
    }

    protected function parseResponse($results){
        $return = array();
        foreach($results as $result){

           $item = new TrackResult();
           $item->setEntryId($result['SongID']);
           $item->setArtist($result['ArtistName']);
           $item->setTitle($result['SongName']);
           $item->setThumbnails('http://images.gs-cdn.net/static/albums/70_'.$result['CoverArtFilename']);
           $item->setTag($this->getResultTag());
           $item->setIcon($this->getDefaultIcon());
           $return[]=$item;
        }

        return $return;
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