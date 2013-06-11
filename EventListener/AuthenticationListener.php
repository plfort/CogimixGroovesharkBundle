<?php

namespace Cogipix\CogimixGroovesharkBundle\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Symfony\Component\Security\Core\Role\Role;

use Doctrine\Common\Persistence\ObjectManager;

use Cogipix\CogimixBundle\Events\AuthenticationEvent;

use Cogipix\CogimixBundle\Events\CogimixEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class AuthenticationListener implements EventSubscriberInterface
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om=$om;
    }

    public static function getSubscribedEvents()
    {
        return array(CogimixEvents::AUTHENTICATTION_SUCCESS => 'onAuthenticationSuccess',
                'security.interactive_login'=>'onInteractiveLogin');

    }

    public function onInteractiveLogin(InteractiveLoginEvent $event){
            $user=$event->getAuthenticationToken()->getUser();
            $groovesharkSession=$this->om->getRepository('CogimixGroovesharkBundle:GroovesharkSession')->findOneByUser($user);
            if($groovesharkSession){
                $event->getRequest()->getSession()->set('gsSession',$groovesharkSession->getSessionId());
            }

    }

    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {

            $user=$event->getToken()->getUser();
            $groovesharkSession=$this->om->getRepository('CogimixGroovesharkBundle:GroovesharkSession')->findOneByUser($user);
            if($groovesharkSession){
                $user->addRole('ROLE_GROOVESHARK');

                //$event->getRequest()->getSession()->set('gsSession',$groovesharkSession->getSessionId());
                $event->getToken()->setAuthenticated(false);
            }
            $this->om->flush();

    }

}
