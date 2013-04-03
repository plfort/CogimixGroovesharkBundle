<?php
namespace Cogipix\CogimixGroovesharkBundle\Entity;
use Cogipix\CogimixBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @author plfort - Cogipix
 *
 */
class GroovesharkSession
{

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Cogipix\CogimixBundle\Entity\User")
     * @var User $user
     */

    protected $user;

    /**
     * @ORM\Column(type="string")
     * @var unknown_type
     */
    protected $sessionId;

    public function getId()
    {
        return $this->id;
    }



    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

}
