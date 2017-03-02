<?php

namespace Nines\UserBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class to build some menus for navigation.
 */
class Builder implements ContainerAwareInterface {

    use ContainerAwareTrait;

    const CARET = ' ▾'; // U+25BE, black down-pointing small triangle.
    
    /**
     * Build a menu for blog posts.
     * 
     * @param FactoryInterface $factory
     * @param array $options
     * @return ItemInterface
     */
    public function userNavMenu(FactoryInterface $factory, array $options) {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav navbar-right',
        ));
        $menu->setAttribute('dropdown', true);

        if (!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $menu->addChild("Login", array(
                'route' => 'fos_user_security_login'
            ));
        } else {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $menu->addChild('user', array(
                'uri' => '#',
                'label' => $user->getUsername() . self::CARET,
            ));
            $menu['user']->setAttribute('dropdown', true);
            $menu['user']->setLinkAttribute('class', 'dropdown-toggle');
            $menu['user']->setLinkAttribute('data-toggle', 'dropdown');
            $menu['user']->setChildrenAttribute('class', 'dropdown-menu');
            $menu['user']->addChild('Profile', array('route' => 'fos_user_profile_show'));
            $menu['user']->addChild('Change password', array('route' => 'fos_user_change_password'));
            $menu['user']->addChild('Logout', array('route' => 'fos_user_security_logout'));
        }

        return $menu;
    }

}
