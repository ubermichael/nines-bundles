<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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
class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
        if ( ! $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->authChecker->isGranted($role);
    }

    private function getUser() {
        if ( ! $this->hasRole('ROLE_USER')) {
            return;
        }

        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Build a menu for blog posts.
     *
     * @return ItemInterface
     */
    public function userMenu(array $options) {
        $name = $options['name'] ?? 'Login';

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav navbar-right',
        ]);

        $menu->setAttribute('dropdown', true);
        $user = $this->getUser();
        if ( ! $this->hasRole('ROLE_USER')) {
            $menu->addChild($name, [
                'route' => 'nines_user_security_login',
            ]);

            return $menu;
        }

        $user = $menu->addChild('user', [
            'uri' => '#',
            'label' => $user->getUsername(),
        ]);

        $user->setAttribute('dropdown', true);
        $user->setLinkAttribute('class', 'dropdown-toggle');
        $user->setLinkAttribute('data-toggle', 'dropdown');
        $user->setChildrenAttribute('class', 'dropdown-menu');

        $user->addChild('Profile', ['route' => 'nines_user_profile_index']);
        $user->addChild('Change password', ['route' => 'nines_user_profile_password']);
        $user->addChild('Logout', ['route' => 'nines_user_security_logout']);

        if ($this->hasRole('ROLE_ADMIN')) {
            $user->addChild('divider', [
                'label' => '',
            ]);
            $user['divider']->setAttributes([
                'role' => 'separator',
                'class' => 'divider',
            ]);

            $user->addChild('users', [
                'label' => 'Users',
                'route' => 'nines_user_admin_index',
            ]);
        }

        return $menu;
    }
}
