<?php

namespace Nines\FeedbackBundle\Menu;

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

    public function navMenu(FactoryInterface $factory, array $options) {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'dropdown-menu',
        ));
        $menu->setAttribute('dropdown', true);

        if ($this->container->get('security.token_storage')->getToken() && $this->container->get('security.authorization_checker')->isGranted('ROLE_COMMENT_ADMIN')) {
            $menu->addChild('Comments', array(
                'route' => 'admin_comment_index',
            ));
            $menu->addChild('Comment States', array(
                'route' => 'admin_comment_status_index',
            ));
        }

        return $menu;
    }

}
