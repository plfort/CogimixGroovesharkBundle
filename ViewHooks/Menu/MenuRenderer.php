<?php
namespace Cogipix\CogimixGroovesharkBundle\ViewHooks\Menu;


use Cogipix\CogimixBundle\ViewHooks\Menu\MenuItemInterface;


class MenuRenderer implements MenuItemInterface{

    public function getMenuItemTemplate()
    {
          return 'CogimixGroovesharkBundle:Menu:menu.html.twig';

    }
}