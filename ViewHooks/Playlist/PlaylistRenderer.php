<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Playlist;
use Cogipix\CogimixGroovesharkBundle\Services\GroovesharkAPI;

use Cogipix\CogimixCommonBundle\ViewHooks\Playlist\PlaylistRendererInterface;
/**
 *
 * @author plfort - Cogipix
 *
 */
class PlaylistRenderer implements PlaylistRendererInterface{

    private $gsApi;

    public function __construct($gsApi){
        $this->gsApi = $gsApi;
    }
    public function getListTemplate(){
        return 'CogimixGroovesharkBundle:Playlist:list.html.twig';

    }

    public function getPlaylists(){
        $playlists=array();
        try{
            $userInfo=$this->gsApi->getUserInfo();

            if(!empty($userInfo)){
                $playlists= $this->gsApi->getUserPlaylists();
            }
        }catch(\Exception $ex){

        }
        return $playlists;
    }

    public function getTag(){
        return 'grooveshark';
    }
}