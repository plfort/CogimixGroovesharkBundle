<?php

namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Playlist;

use plfort\GroovesharkAPI\GroovesharkException;
use Cogipix\CogimixCommonBundle\ViewHooks\Playlist\PlaylistRendererInterface;
use Cogipix\CogimixCommonBundle\Utils\LoggerAwareInterface;

/**
 *
 * @author plfort - Cogipix
 *
 */
class PlaylistRenderer implements PlaylistRendererInterface, LoggerAwareInterface {
	private $gsApi;
	private $logger;
	public function __construct($gsApi) {
		$this->gsApi = $gsApi;
	}
	public function getListTemplate() {
		return 'CogimixGroovesharkBundle:Playlist:list.html.twig';
	}

	public function getPlaylists($alphaSort = true) {
		$playlists = array ();
		try {
			$userInfo = $this->gsApi->getUserInfo ();

			if (! empty ( $userInfo )) {
				$playlists = $this->gsApi->getUserPlaylists ();
				if($alphaSort === true){
					uasort ( $playlists, function ($i, $j) {
						$a = $i ['PlaylistName'];
						$b = $j ['PlaylistName'];
						if ($a == $b)
							return 0;
						elseif ($a > $b)
						return 1;
						else
							return - 1;
					} );
				}

			}
		} catch ( GroovesharkException $ex ) {

			$this->logger->error ( $ex );
		}
		return $playlists;
	}
	public function getTag() {
		return 'grooveshark';
	}
	public function setLogger($logger) {
		$this->logger = $logger;
	}
}