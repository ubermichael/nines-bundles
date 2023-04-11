<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Menu;

use Knp\Menu\ItemInterface;
use Nines\UtilBundle\Menu\AbstractBuilder;

/**
 * Class to build some menus for navigation.
 */
class Builder extends AbstractBuilder {
    /**
     * @param array<string,mixed> $options
     */
    public function userMenu(array $options) : ItemInterface {
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

        if ($this->hasRole('ROLE_USER_ADMIN')) {
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
