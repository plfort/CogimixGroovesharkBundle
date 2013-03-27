<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;


class PlaylistRenderer{

    private $gsApi;


    public function __construct($gsApi){
        $this->gsApi = $gsApi;
    }
    public function getListTemplate(){
        return 'CogimixGroovesharkBundle:Playlist:list.html.twig';

    }

    public function getPlaylists(){
        $userInfo=$this->gsApi->getUserInfo();
        $playlists=array();
        if(!empty($userInfo)){
            $playlists= $this->gsApi->getUserPlaylists();
        }
        return $playlists;
    }
}