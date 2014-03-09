<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Menu;
use Cogipix\CogimixCommonBundle\ViewHooks\Menu\MenuItemInterface;
use Cogipix\CogimixCommonBundle\ViewHooks\Menu\AbstractMenuItem;

/**
 *
 * @author plfort - Cogipix
 *
 */
class MenuItem extends AbstractMenuItem
{

    public function getMenuItemTemplate()
    {
          return 'CogimixGroovesharkBundle:Menu:menu.html.twig';

    }

	 /* (non-PHPdoc)
	  * @see \Cogipix\CogimixCommonBundle\ViewHooks\Menu\MenuItemInterface::getName()
	  */
	 public function getName() {
	 	return 'grooveshark';

	 }

}