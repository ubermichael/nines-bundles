<?php

namespace Nines\UserBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class to build some menus for navigation.
 */
class Builder implements ContainerAwareInterface {

    use ContainerAwareTrait;

    const CARET = ' ▾'; // U+25BE, black down-pointing small triangle.

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage) {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    private function hasRole($role) {
        if (!$this->tokenStorage->getToken()) {
            return false;
        }
        return $this->authChecker->isGranted($role);
    }
    
    private function getUser() {
        if( ! $this->hasRole('ROLE_USER')) {
            return null;
        }
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Build a menu for blog posts.
     * 
     * @param FactoryInterface $factory
     * @param array $options
     * @return ItemInterface
     */
    public function userNavMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav navbar-right',
        ));
        $menu->setAttribute('dropdown', true);
        $user = $this->getUser();
        if (!$this->hasRole('ROLE_USER')) {
            $menu->addChild("Login", array(
                'route' => 'fos_user_security_login'
            ));
            return $menu;
        }

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

        if ($this->hasRole('ROLE_ADMIN')) {
            $menu['user']->addChild('divider', array(
                'label' => '',
            ));
            $menu['user']['divider']->setAttributes(array(
                'role' => 'separator',
                'class' => 'divider',
            ));

            $menu['user']->addChild('users', array(
                'label' => 'Users',
                'route' => 'user',
            ));
        }
        return $menu;
    }

}
