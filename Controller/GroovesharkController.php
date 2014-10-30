<?php
namespace Cogipix\CogimixGroovesharkBundle\Controller;

use Cogipix\CogimixGroovesharkBundle\Entity\GroovesharkSession;
use Symfony\Component\HttpFoundation\Response;
use Cogipix\CogimixCommonBundle\Entity\TrackResult;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Session\Session;
use Cogipix\CogimixCommonBundle\Utils\AjaxResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use plfort\GroovesharkAPI\GroovesharkException;

/**
 * @Route("/grooveshark")
 *
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
        if ($request->isXmlHttpRequest()) {
            $gsApi = $this->get('grooveshark.api');
            try {
                $result = $gsApi->getStreamKeyStreamServer($songId);
                $response->setSuccess(true);
                $response->addData('stream', $result);
            } catch (\Exception $ex) {
                $response->setSuccess(false);
                $this->get('logger')->err($ex->getMessage());
            }
        }
        return $response->createResponse();
    }

    /**
     * @Route("/markStreamKeyOver30Sec/{streamKey}/{serverId}",name="_grooveshark_markStreamKeyOver30Sec",options={"expose"=true})
     */
    public function markStreamKeyOver30SecAction(Request $request, $streamKey, $serverId)
    {
        $response = new AjaxResult();
        if ($request->isXmlHttpRequest()) {
            $gsApi = $this->get('grooveshark.api');
            try {
                $result = $gsApi->markStreamKeyOver30Secs($streamKey, $serverId);
                $response->setSuccess(true);
                // $this->get('logger')->info($result);
            } catch (\Exception $ex) {
                $this->get('logger')->err($ex);
            }
        }
        return $response->createResponse();
    }

    /**
     * @Route("/markSongComplete/{streamKey}/{serverId}/{songId}",name="_grooveshark_markSongComplete",options={"expose"=true})
     */
    public function markSongCompleteAction(Request $request, $streamKey, $serverId, $songId)
    {
        $response = new AjaxResult();
        if ($request->isXmlHttpRequest()) {
            $gsApi = $this->get('grooveshark.api');
            try {
                $result = $gsApi->markSongComplete($songId, $streamKey, $serverId);
                $response->setSuccess(true);
                // $this->get('logger')->info($result);
            } catch (\Exception $ex) {
                $this->get('logger')->err($ex);
            }
        }
        
        return $response->createResponse();
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/logout",name="_grooveshark_logout",options={"expose"=true})
     */
    public function logoutAction(Request $request)
    {
        $response = new AjaxResult();
        $gsApi = $this->get('grooveshark.api');
        try {
            if ($gsApi->logout()) {
                $this->removeUserSession();
                $response->setSuccess(true);
                $response->addData('loginLink', $this->renderView('CogimixGroovesharkBundle:Login:loginLink.html.twig'));
            } else {
                $this->get('logger')->err($gsApi::$lastError);
            }
        } catch (GroovesharkException $ex) {
            $this->removeUserSession();
            $response->setSuccess(true);
            $response->addData('loginLink', $this->renderView('CogimixGroovesharkBundle:Login:loginLink.html.twig'));
        }
        return $response->createResponse();
    }

    private function removeUserSession()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $groovesharkSession = $em->getRepository('CogimixGroovesharkBundle:GroovesharkSession')->findOneByUser($user);
        $user->removeRole('ROLE_GROOVESHARK');
        if ($groovesharkSession) {
            $em->remove($groovesharkSession);
        }
        $em->flush();
        
        $this->get('security.context')
            ->getToken()
            ->setAuthenticated(false);
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/oauth/login",name="_grooveshark_get_oauth_login_url",options={"expose"=true})
     */
    public function oAuthGetLoginUrl(Request $request)
    {
        $response = new AjaxResult();
        $gsApi = $this->get('grooveshark.api');
        $authorizeUrl = $gsApi->getOAuthAutenticateUrl($this->generateUrl('_grooveshark_login', array(), true));
        $response->setSuccess(true);
        $response->addData('authUrl', $authorizeUrl);
        return $response->createResponse();
    }

    /**
     * @Secure(roles="ROLE_USER")
     * @Route("/login",name="_grooveshark_login",options={"expose"=true})
     * @Template("CogimixGroovesharkBundle:Login:finish.html.twig")
     * 
     * @param unknown_type $username            
     * @param unknown_type $password            
     */
    public function loginAction(Request $request)
    {
        $response = new AjaxResult();
        $token = $request->query->get('token');
        $success = false;
        $gsApi = $this->get('grooveshark.api');
        if ($gsApi->authenticateToken($token)) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $groovesharkSession = $em->getRepository('CogimixGroovesharkBundle:GroovesharkSession')->findOneByUser($user);
            if ($groovesharkSession === null) {
                $groovesharkSession = new GroovesharkSession();
                $groovesharkSession->setUser($user);
            }
            
            $groovesharkSession->setSessionId($gsApi->getSession());
            $groovesharkSession->setToken($token);
            $user->addRole('ROLE_GROOVESHARK');
            $this->getDoctrine()
                ->getManager()
                ->persist($groovesharkSession);
            $this->getDoctrine()
                ->getManager()
                ->flush();
            
            $response->setSuccess(true);
            $this->get('security.context')
                ->getToken()
                ->setAuthenticated(false);
            $success = true;
        }
        return array(
            'success' => $success
        );
    }

    /**
     * @Secure(roles="ROLE_GROOVESHARK")
     * @Route("/playlist",name="_grooveshark_playlist")
     * @Template("CogimixGroovesharkBundle:Playlist:list.html.twig")
     */
    public function getPlaylistAction()
    {
        $gsApi = $this->get('grooveshark.api');
        $playlists = array();
        try {
            $userInfo = $gsApi->getUserInfo();
            if (! empty($userInfo)) {
                $playlists = $gsApi->getUserPlaylists();
            } else {
                
                return $this->render('CogimixGroovesharkBundle:Login:loginButton.html.twig');
            }
        } catch (\Exception $ex) {}
        return array(
            'list' => $playlists
        );
    }


    /**
     * @Secure(roles="ROLE_GROOVESHARK")
     * @Route("/playlist/{id}",name="_grooveshark_playlist_songs",options={"expose"=true})
     */
    public function getPlaylistSongsAction($id)
    {
        $ajaxResponse = new AjaxResult();
        $gsApi = $this->get('grooveshark.api');
        $songs = $gsApi->getPlaylistSongs($id);
        $return = $this->get('grooveshark_music.result_builder')->createArrayFromGroovesharkTracks($songs);
        $ajaxResponse->setSuccess(true);
        $ajaxResponse->addData('tracks', $return);
        return $ajaxResponse->createResponse();
    }
}
