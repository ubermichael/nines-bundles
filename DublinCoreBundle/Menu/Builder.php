<?php

namespace Nines\DublinCoreBundle\Menu;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

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

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, EntityManagerInterface $em) {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
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

    public function dcMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'dropdown-menu',
        ));
        $menu->setAttribute('dropdown', true);
        $menu->addChild('elements', array(
            'label' => 'Elements',
            'route' => 'element_index',
        ));


        return $menu;
    }

}
