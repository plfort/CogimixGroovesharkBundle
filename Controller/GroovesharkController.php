<?php
namespace Cogipix\CogimixGroovesharkBundle\Controller;

use Cogipix\CogimixGroovesharkBundle\Entity\GroovesharkSession;

use Symfony\Component\HttpFoundation\Response;

use Cogipix\CogimixCommonBundle\Entity\TrackResult;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Cogipix\CogimixGroovesharkBundle\Form\Grooveshark\LoginFormType;

use Symfony\Component\HttpFoundation\Session\Session;

use Cogipix\CogimixCommonBundle\Utils\AjaxResult;

use Symfony\Component\HttpFoundation\Request;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * @Route("/grooveshark")
 * @author plfort - Cogipix
 *
 */
class GroovesharkController extends Controller
{
    /**
     * @Route("/getsong/{songId}",name="_grooveshark_getsong",options={"expose"=true})
     */
    public function getSongAction(Request $request, $songId)
    {
       $response = new AjaxResult();
       if($request->isXmlHttpRequest()){
           $gsApi = $this->get('grooveshark.api');
           try{
               $result= $gsApi->getStreamKeyStreamServer($songId);
               $response->setSuccess(true);
               $response->addData('stream', $result);
           }catch(\Exception $ex){
               $response->setSuccess(false);
               $this->get('logger')->err($ex->getMessage());
           }
       }
       return $response->createResponse();
    }

    /**
     * @Route("/markStreamKeyOver30Sec/{streamKey}/{serverId}",name="_grooveshark_markStreamKeyOver30Sec",options={"expose"=true})
     */
    public function markStreamKeyOver30SecAction(Request $request, $streamKey,$serverId)
    {
        $response = new AjaxResult();
        if($request->isXmlHttpRequest()){
            $gsApi = $this->get('grooveshark.api');
            try{
               $result= $gsApi->markStreamKeyOver30Secs($streamKey,$serverId);
               $response->setSuccess(true);
                //$this->get('logger')->info($result);
            }catch(\Exception $ex){
                $this->get('logger')->err($ex);
            }
        }
        return $response->createResponse();
    }

    /**
     * @Route("/markSongComplete/{streamKey}/{serverId}/{songId}",name="_grooveshark_markSongComplete",options={"expose"=true})
     */
    public function markSongCompleteAction(Request $request, $streamKey,$serverId,$songId)
    {
        $response = new AjaxResult();
        if($request->isXmlHttpRequest()){
            $gsApi = $this->get('grooveshark.api');
            try{
                $result= $gsApi->markSongComplete($songId,$streamKey,$serverId);
                $response->setSuccess(true);
                //$this->get('logger')->info($result);
            }catch(\Exception $ex){
                $this->get('logger')->err($ex);
            }
        }

         return $response->createResponse();
    }

    /**
     *  @Secure(roles="ROLE_USER")
     *  @Route("/logout",name="_grooveshark_logout",options={"expose"=true})
     */
    public function logoutAction(Request $request){
        $response = new AjaxResult();
        $gsApi = $this->get('grooveshark.api');
        if($gsApi->logout()){
            $em =  $this->getDoctrine()->getEntityManager();
            $user = $this->getUser();
            $groovesharkSession= $em->getRepository('CogimixGroovesharkBundle:GroovesharkSession')->findOneByUser($user);
            $user->removeRole('ROLE_GROOVESHARK');
            if($groovesharkSession){
                $em->remove($groovesharkSession);
                $response->setSuccess(true);
                $response->addData('loginLink',$this->renderView('CogimixGroovesharkBundle:Login:loginLink.html.twig'));
            }
            $em->flush();
            $this->get('security.context')->getToken()->setAuthenticated(false);
        }else{
            $this->get('logger')->info($gsApi::$lastError);
        }

        return $response->createResponse();
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
                if($gsApi->login($data['login'],$data['password'])!==false) {
                    $em =  $this->getDoctrine()->getEntityManager();
                    $user = $this->getUser();
                   $groovesharkSession= $em->getRepository('CogimixGroovesharkBundle:GroovesharkSession')->findOneByUser($user);
                   if($groovesharkSession === null){
                       $groovesharkSession = new GroovesharkSession();
                       $groovesharkSession->setUser($user);
                   }

                    $groovesharkSession->setSessionId($gsApi->getSession());
                    $user->addRole('ROLE_GROOVESHARK');
                    $this->getDoctrine()->getEntityManager()->persist($groovesharkSession);
                    $this->getDoctrine()->getEntityManager()->flush();
                    $playlists= $gsApi->getUserPlaylists();
                    $response->setSuccess(true);
                    $this->get('security.context')->getToken()->setAuthenticated(false);
                    $response->addData('playlistsHtml', $this->renderView('CogimixGroovesharkBundle:Playlist:list.html.twig',array('playlists'=>$playlists)));
                    $response->addData('logoutLink',$this->renderView('CogimixGroovesharkBundle:Login:logoutLink.html.twig'));
                    return $response->createResponse();
                }else{
                    $this->get('logger')->info($gsApi::$lastError);
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
     *  @Secure(roles="ROLE_GROOVESHARK")
     *  @Route("/playlist",name="_grooveshark_playlist")
     *  @Template("CogimixGroovesharkBundle:Playlist:list.html.twig")
     */
    public function getPlaylistAction(){
       $gsApi = $this->get('grooveshark.api');
       $playlists=array();
       try{
           $userInfo=$gsApi->getUserInfo();
           if(!empty($userInfo)){
               $playlists= $gsApi->getUserPlaylists();
           }else{

                return $this->render('CogimixGroovesharkBundle:Login:loginButton.html.twig');
           }
       }catch(\Exception $ex){

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
     *  @Secure(roles="ROLE_GROOVESHARK")
     *  @Route("/playlist/{id}",name="_grooveshark_playlist_songs",options={"expose"=true})
     */
    public function getPlaylistSongsAction($id){
        $ajaxResponse= new AjaxResult();
        $gsApi = $this->get('grooveshark.api');
        $songs=  $gsApi->getPlaylistSongs($id);
        $return=$this->get('grooveshark_music.result_builder')->createArrayFromGroovesharkTracks($songs);
        $ajaxResponse->setSuccess(true);
        $ajaxResponse->addData('tracks', $return);
        return $ajaxResponse->createResponse();

    }

}
