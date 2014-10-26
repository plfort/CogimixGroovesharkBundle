<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use Cogipix\CogimixCommonBundle\Entity\TrackResult;
use Cogipix\CogimixCommonBundle\MusicSearch\AbstractMusicSearch;
use plfort\GroovesharkAPI\GroovesharkException;

class GroovesharkSongMusicSearch extends AbstractMusicSearch{


    private $gsApi;
    private $resultBuilder;
    private $query=null;

    public function __construct($gsApi,$resultBuilder){
        $this->gsApi=$gsApi;
        $this->resultBuilder=$resultBuilder;
    }

    protected function parseResponse($results){
        return $this->resultBuilder->createArrayFromGroovesharkTracks($results);
    }

    protected function buildQuery(){
        $this->query = $this->searchQuery->getSongQuery();
    }

    protected function executeQuery(){
        $this->logger->info('Groovshark executeQuery');
        $results = null;
		try{
        	$results= $this->gsApi->getSongSearchResults($this->query);
		}catch(\Exception $ex){
			$this->logger->err($ex);
		}

        if($results){
            return $this->parseResponse($results);
        }else{
           // $this->logger->err(\gsSearch::$lastError);
        }
    }

    protected function executePopularQuery(){
        $popularSongs = array();
        try{
            $popularSongs= $this->gsApi->getPopularSongsToday();
        }catch(GroovesharkException $ex){
            $this->logger->err($ex);
        }
       
        return $this->parseResponse($popularSongs);
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
        return '/bundles/cogimixgrooveshark/images/gs-icon.png';
    }


}

?>