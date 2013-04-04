<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Playlist;
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
        $userInfo=$this->gsApi->getUserInfo();
        $playlists=array();
        if(!empty($userInfo)){
            $playlists= $this->gsApi->getUserPlaylists();
        }
        return $playlists;
    }
}