<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Menu;

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
        $title = $options['title'] ?? 'Feedback';

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav',
        ]);
        $menu->setAttribute('dropdown', true);

        $feedback = $menu->addChild('feedback', [
            'uri' => '#',
            'label' => $title,
        ]);
        $feedback->setAttribute('dropdown', true);
        $feedback->setLinkAttribute('class', 'dropdown-toggle');
        $feedback->setLinkAttribute('data-toggle', 'dropdown');
        $feedback->setChildrenAttribute('class', 'dropdown-menu');

        $feedback->addChild('Comments', [
            'route' => 'nines_feedback_comment_index',
        ]);
        $feedback->addChild('Comment Notes', [
            'route' => 'nines_feedback_comment_note_index',
        ]);
        $divider = $feedback->addChild('divider', [
            'label' => '',
        ]);
        $divider->setAttributes([
            'role' => 'separator',
            'class' => 'divider',
        ]);
        $feedback->addChild('Comment States', [
            'route' => 'nines_feedback_comment_status_index',
        ]);

        return $menu;
    }
}
