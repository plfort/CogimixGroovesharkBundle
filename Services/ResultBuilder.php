<?php
namespace Cogipix\CogimixGroovesharkBundle\Services;
use Cogipix\CogimixCommonBundle\Entity\TrackResult;

use Cogipix\CogimixCommonBundle\ResultBuilder\ResultBuilderInterface;
class ResultBuilder implements ResultBuilderInterface
{

    private $basePathThumbnails = 'http://images.gs-cdn.net/static/albums/90_';
    public function createFromGroovesharkTrack($groovesharkTrack)
    {
        $item =null;
        if(!empty($groovesharkTrack)){
            $item = new TrackResult();
            $item->setEntryId($groovesharkTrack['SongID']);
            $item->setArtist($groovesharkTrack['ArtistName']);
            $item->setTitle($groovesharkTrack['SongName']);
            $item->setThumbnails($this->basePathThumbnails.$groovesharkTrack['CoverArtFilename']);
            $item->setTag($this->getResultTag());
            $item->setIcon($this->getDefaultIcon());

        }
        return $item;
    }

    public function createArrayFromGroovesharkTracks($groovesharkTracks)
    {
        $tracks =array();
        if(!empty($groovesharkTracks)){
            foreach($groovesharkTracks as $groovesharkTrack){
                $item = $this->createFromGroovesharkTrack($groovesharkTrack);
                if($item !==null){
                    $tracks[]=$item;
                }
            }
        }
        return $tracks;
    }


    public function getResultTag(){
        return 'gs';
    }

    public function getDefaultIcon(){
        return '/bundles/cogimixgrooveshark/images/gs-icon.png';
    }

}
