<?php

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
        if (!$this->hasRole('ROLE_USER')) {
            return null;
        }
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Build a menu for blog posts.
     *
     * @param array $options
     * @return ItemInterface
     */
    public function navMenu(array $options) {
        if (!$this->hasRole('ROLE_COMMENT_ADMIN')) {
            return;
        }
        $title = 'Feedback';
        if (isset($options['title'])) {
            $title = $options['title'];
        }
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav',
        ));
        $menu->setAttribute('dropdown', true);

        $feedback = $menu->addChild('feedback', array(
            'uri' => '#',
            'label' => $title . self::CARET,
        ));
        $feedback->setAttribute('dropdown', true);
        $feedback->setLinkAttribute('class', 'dropdown-toggle');
        $feedback->setLinkAttribute('data-toggle', 'dropdown');
        $feedback->setChildrenAttribute('class', 'dropdown-menu');

        $feedback->addChild('Comments', array(
            'route' => 'admin_comment_index',
        ));
        $feedback->addChild('Comment Notes', array(
            'route' => 'admin_comment_note_index',
        ));
        $divider = $feedback->addChild('divider', array(
            'label' => '',
        ));
        $divider->setAttributes(array(
            'role' => 'separator',
            'class' => 'divider',
        ));
        $feedback->addChild('Comment States', array(
            'route' => 'admin_comment_status_index',
        ));

        return $menu;
    }

}
