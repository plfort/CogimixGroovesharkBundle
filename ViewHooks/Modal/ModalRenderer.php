<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Modal;

use Cogipix\CogimixBundle\ViewHooks\Modal\ModalItemInterface;

class ModalRenderer implements ModalItemInterface
{

    public function getModalTemplate()
    {
        return 'CogimixGroovesharkBundle:Modal:modals.html.twig';

    }

}
