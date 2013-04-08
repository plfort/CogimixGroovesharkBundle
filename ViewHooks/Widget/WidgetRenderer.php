<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Widget;
use Cogipix\CogimixCommonBundle\ViewHooks\Widget\WidgetRendererInterface;

/**
 *
 * @author plfort - Cogipix
 *
 */
class WidgetRenderer implements WidgetRendererInterface
{


    public function getWidgetTemplate()
    {
        return 'CogimixGroovesharkBundle:Widget:widget.html.twig';
    }

    public function getParameters(){
        return array();
    }

}
