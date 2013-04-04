<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Modal;

use Cogipix\CogimixCommonBundle\ViewHooks\Modal\ModalItemInterface;
/**
 *
 * @author plfort - Cogipix
 *
 */
class ModalRenderer implements ModalItemInterface
{

    public function getModalTemplate()
    {
        return 'CogimixGroovesharkBundle:Modal:modals.html.twig';

    }

}
