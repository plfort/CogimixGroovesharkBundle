<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;

use Cogipix\CogimixCommonBundle\Model\ParsedUrl;

use Cogipix\CogimixCommonBundle\MusicSearch\UrlSearcherInterface;

class GroovesharkUrlSearch implements UrlSearcherInterface
{
    private $regexHost = '#^(?:www\.)?(?:grooveshark\.com)#';
    private $resultBuilder;
    private $gsApi;

    public function __construct(ResultBuilder $resultBuilder,GroovesharkAPI $gsApi){
        $this->resultBuilder = $resultBuilder;
        $this->gsApi = $gsApi;
    }


    public function canParse($host)
    {

        preg_match($this->regexHost, $host,$matches);

       return isset($matches[0]) ? $matches[0] : false;

    }

    public function searchByUrl(ParsedUrl $url)
    {

        if( ($match = $this->canParse($url->host)) !== false){

            $path = $url->path;
            if(empty($url->path)){
                if($url->fragment){
                    $fragment=strstr($url->fragment,'/');
                    $trimedFragment=trim($fragment, "/");
                    $parsedFragment=parse_url($trimedFragment);
                    if(isset($parsedFragment['path']) && !empty($parsedFragment['path'])){
                        $path = explode('/',$parsedFragment['path']);
                    }
                }
            }


            $result = null;
            if(in_array('playlist', $path)){
               $result= $this->gsApi->getPlaylistSongs(end($path));
            }
            if(in_array('artist', $path)){
                $result= $this->gsApi->getArtistPopularSongs(end($path));
            }
            if(in_array('album', $path)){
                $result= $this->gsApi->getAlbumSongs(end($path));
            }
            /*
            if(in_array('s', $path)){
               $result= $this->gsApi->getSongInfo(end($path));
            }*/


            return  $this->resultBuilder->createArrayFromGroovesharkTracks($result);
        }else{
            return null;
        }


    }

}
