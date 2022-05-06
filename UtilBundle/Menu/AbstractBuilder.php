<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Nines\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AbstractBuilder implements ContainerAwareInterface {
    use ContainerAwareTrait;

    protected ?FactoryInterface $factory = null;

    protected ?AuthorizationCheckerInterface $authChecker = null;

    protected ?TokenStorageInterface $tokenStorage = null;

    protected function hasRole(string $role) : bool {
        if ( ! $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->authChecker->isGranted($role);
    }

    protected function getUser() : ?User {
        if ( ! $this->hasRole('ROLE_USER')) {
            return null;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ( ! $user instanceof User) {
            return null;
        }

        return $user;
    }

    protected function dropdown(string $name) : ItemInterface {
        $root = $this->factory->createItem('root');
        $root->setChildrenAttributes([
            'class' => 'nav navbar-nav',
        ]);
        $root->setAttribute('dropdown', true);

        $menu = $root->addChild('feedback', [
            'uri' => '#',
            'label' => $name,
        ]);
        $menu->setAttribute('dropdown', true);
        $menu->setLinkAttribute('class', 'dropdown-toggle');
        $menu->setLinkAttribute('data-toggle', 'dropdown');
        $menu->setChildrenAttribute('class', 'dropdown-menu');

        return $menu;
    }

    protected function addDivider(ItemInterface $item, ?string $name = 'divider') : void {
        $divider = $item->addChild($name, [
            'label' => '',
        ]);
        $divider->setAttributes([
            'role' => 'separator',
            'class' => 'divider',
        ]);
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setFactory(FactoryInterface $factory) : void {
        $this->factory = $factory;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setAuthChecker(AuthorizationCheckerInterface $authChecker) : void {
        $this->authChecker = $authChecker;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage) : void {
        $this->tokenStorage = $tokenStorage;
    }
}
