<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Widget;

use Cogipix\CogimixCommonBundle\ViewHooks\Widget\WidgetRendererInterface;
use plfort\GroovesharkAPI\GroovesharkException;
/**
 *
 * @author plfort - Cogipix
 *
 */
class WidgetRenderer implements WidgetRendererInterface
{

    private $gsApi;
    public function __construct($gsAPi){
        $this->gsApi = $gsAPi;
    }

    public function getWidgetTemplate()
    {
        return 'CogimixGroovesharkBundle:Widget:widget.html.twig';
    }

    public function getParameters(){
        $isAnywhere = false;
        try{
            $userInfo = $this->gsApi->getUserInfo();

            if(isset($userInfo['IsAnywhere']) && $userInfo['IsAnywhere']==true){
                $isAnywhere = true;
            }
        }catch(GroovesharkException $ex){

        }
        return array('isAnywhere'=>$isAnywhere);
    }

}
