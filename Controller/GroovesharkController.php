<?php
namespace Cogipix\CogimixGroovesharkBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Cogipix\CogimixBundle\Entity\TrackResult;

use Cogipix\CogimixBundle\Controller\AbstractController;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Cogipix\CogimixGroovesharkBundle\Form\Grooveshark\LoginFormType;


use Cogipix\CogimixGroovesharkBundle\lib\Grooveshark\gsUser;

use Symfony\Component\HttpFoundation\Session\Session;

use Cogipix\CogimixBundle\Utils\AjaxResult;

use Symfony\Component\HttpFoundation\Request;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * @Route("/grooveshark")
 * @author plfort - Cogipix
 *
 */
class GroovesharkController extends AbstractController
{
    /**
     * @Route("/getsong/{songId}",name="_grooveshark_getsong",options={"expose"=true})
     */
    public function getSongAction(Request $request, $songId)
    {
       $response = new AjaxResult();
       $gsApi = $this->get('grooveshark.api');
       try{
       $result= $gsApi->getStreamKeyStreamServer($songId);
       $response->setSuccess(true);
       $response->addData('stream', $result);
       }catch(\Excpetion $ex){
           $response->setSuccess(false);
       }

       return $response->createResponse();
    }

    /**
     * @Route("/markStreamKeyOver30Sec/{streamKey}/{serverId}",name="_grooveshark_markStreamKeyOver30Sec",options={"expose"=true})
     */
    public function markStreamKeyOver30SecAction(Request $request, $streamKey,$serverId)
    {

        $gsApi = $this->get('grooveshark.api');
        try{
          $result= $gsApi->markStreamKeyOver30Secs($streamKey,$serverId);
            $this->get('logger')->info($result);
        }catch(\Excpetion $ex){
            $this->get('logger')->info($ex);
        }

        return new Response();
    }

    /**
     * @Route("/markSongComplete/{streamKey}/{serverId}/{songId}",name="_grooveshark_markSongComplete",options={"expose"=true})
     */
    public function markSongCompleteAction(Request $request, $streamKey,$serverId,$songId)
    {

        $gsApi = $this->get('grooveshark.api');
        try{
            $result= $gsApi->markSongComplete($songId,$streamKey,$serverId);
            $this->get('logger')->info($result);
        }catch(\Excpetion $ex){
            $this->get('logger')->info($ex);
        }

        return new Response();
    }

    /**
     *  @Secure(roles="ROLE_USER")
     *  @Route("/login",name="_grooveshark_login",options={"expose"=true})
     * @param unknown_type $username
     * @param unknown_type $password
     */
    public function loginAction(Request $request){

        $response = new AjaxResult();
        $form = $this->createForm(new LoginFormType());
        /**
         * @var GroovesharkAPI $gsApi
         */

        if($request->getMethod()=='POST'){
            $form->bind($request);
            if($form->isValid()){
                $data = $form->getData();
            $gsApi = $this->get('grooveshark.api');
            $gsUser = new gsUser();

            $gsUser->setUsername($data['login']);
            $gsUser->setTokenFromPassword($data['password']);
                if($gsApi->authenticateUser($gsUser)!==false) {
                    $user = $this->getCurrentUser();
                    $user->setGroovesharkSession($gsApi->getSession());
                    $response->setSuccess(true);
                    $this->getDoctrine()->getEntityManager()->flush();
                    $playlists= $gsApi->getUserPlaylists();
                    $response->addData('playlistsHtml', $this->renderView('CogimixGroovesharkBundle:Playlist:list.html.twig',array('list'=>$playlists)));

                    return $response->createResponse();
                }else{
                    $this->get('logger')->debug($gsApi::$lastError);
                    $response->setSuccess(false);
                    $response->addData('htmlForm', $this->renderView('CogimixGroovesharkBundle:Login:loginForm.html.twig',array('form'=>$form->createView(),'error'=>"Invalid login or password")));
                    return $response->createResponse();
                }
            }
        }
        $response->setSuccess(true);
        $response->addData('htmlForm', $this->renderView('CogimixGroovesharkBundle:Login:loginForm.html.twig',array('form'=>$form->createView())));
       return $response->createResponse();

    }
    /**
     *  @Route("/playlist",name="_grooveshark_playlist")
     *  @Template("CogimixGroovesharkBundle:Playlist:list.html.twig")
     */
    public function getPlaylistAction(){
       $gsApi = $this->get('grooveshark.api');
       $userInfo=$gsApi->getUserInfo();
       $playlists=array();
       if(!empty($userInfo)){
           $playlists= $gsApi->getUserPlaylists();
       }else{

            return $this->render('CogimixGroovesharkBundle:Login:loginButton.html.twig');
       }
       return array('list'=>$playlists);
    }
    /**
     * @Route()
     */
    public function getLoginForm(){
        $form = $this->createForm(new LoginFormType());
        return $this->render('CogimixGroovesharkBundle:Login:loginButton.html.twig',array('form'=>$form));
    }

    /**
     *  @Route("/playlist/{id}",name="_grooveshark_playlist_songs",options={"expose"=true})
     */
    public function getPlaylistSongsAction($id){
        $ajaxResponse= new AjaxResult();
        $gsApi = $this->get('grooveshark.api');
        $songs= $gsApi->getPlaylistSongs($id);
        $return = array();
        foreach($songs as $result){
           $item = new TrackResult();
           $item->setTag('gs');
           $item->setEntryId($result['SongID']);
           $item->setArtist($result['ArtistName']);
           $item->setTitle($result['SongName']);
           $item->setIcon('bundles/cogimixgrooveshark/images/gs-icon.png');
           $item->setThumbnails('http://images.gs-cdn.net/static/albums/70_'.$result['CoverArtFilename']);
           $return[]=$item;
        }
        $ajaxResponse->setSuccess(true);
        $ajaxResponse->addData('tracks', $return);
        return $ajaxResponse->createResponse();

    }
}
