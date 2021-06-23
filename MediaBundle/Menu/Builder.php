<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Menu;

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

    /**
     * Build a menu for blog posts.
     *
     * @return ItemInterface
     */
    public function navMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav',
        ]);
        $menu->setAttribute('dropdown', true);

        $media = $menu->addChild('media', [
            'uri' => '#',
            'label' => 'Media',
        ]);
        $media->setAttribute('dropdown', true);
        $media->setLinkAttribute('class', 'dropdown-toggle');
        $media->setLinkAttribute('data-toggle', 'dropdown');
        $media->setChildrenAttribute('class', 'dropdown-menu');

        $media->addChild('Audio Files', [
            'route' => 'nines_media_audio_index',
        ]);
        $media->addChild('Citations', [
            'route' => 'nines_media_citation_index',
        ]);
        $media->addChild('Images', [
            'route' => 'nines_media_image_index',
        ]);
        $media->addChild('Links', [
            'route' => 'nines_media_link_index',
        ]);

        return $menu;
    }
}
