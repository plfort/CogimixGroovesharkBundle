<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Widget;
use Cogipix\CogimixGroovesharkBundle\Services\GroovesharkAPI;

use Cogipix\CogimixCommonBundle\ViewHooks\Widget\WidgetRendererInterface;

/**
 *
 * @author plfort - Cogipix
 *
 */
class WidgetRenderer implements WidgetRendererInterface
{

    private $gsApi;
    public function __construct(GroovesharkAPI $gsAPi){
        $this->gsApi = $gsAPi;
    }

    public function getWidgetTemplate()
    {
        return 'CogimixGroovesharkBundle:Widget:widget.html.twig';
    }

    public function getParameters(){
        $userInfo = $this->gsApi->getUserInfo();
        $isAnywhere = false;
        if(isset($userInfo['IsAnywhere']) && $userInfo['IsAnywhere']==true){
            $isAnywhere = true;
        }
        return array('isAnywhere'=>$isAnywhere);
    }

}
