<?php

namespace Nines\BlogBundle\Menu;

use Doctrine\ORM\EntityManagerInterface;
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

    /**
     *
     * @var EntityManagerInterface
     */
    private $em;

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
     * @param array $options
     * @return ItemInterface
     */
    public function postNavMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav',
        ));
        $menu->setAttribute('dropdown', true);

        $status = $this->em->getRepository('NinesBlogBundle:PostStatus')->findOneBy(array(
            'public' => true,
        ));
        $posts = $this->em->getRepository('NinesBlogBundle:Post')->findBy(
                array('status' => $status),
                array('id' => 'DESC')
        );
        $title = 'Announcements';
        if(isset($options['title'])) {
            $title = $options['title'];
        }

        $menu->addChild('announcements', array(
            'uri' => '#',
            'label' => $title . self::CARET
        ));
        $menu['announcements']->setAttribute('dropdown', true);
        $menu['announcements']->setLinkAttribute('class', 'dropdown-toggle');
        $menu['announcements']->setLinkAttribute('data-toggle', 'dropdown');
        $menu['announcements']->setChildrenAttribute('class', 'dropdown-menu');

        foreach ($posts as $post) {
            $menu['announcements']->addChild($post->getTitle(), array(
                'route' => 'post_show',
                'routeParameters' => array(
                    'id' => $post->getId(),
                )
            ));
        }
        $menu['announcements']->addChild('divider', array(
            'label' => '',
        ));
        $menu['announcements']['divider']->setAttributes(array(
            'role' => 'separator',
            'class' => 'divider',
        ));

        $menu['announcements']->addChild('All Announcements', array(
            'route' => 'post_index',
        ));

        if ($this->hasRole('ROLE_BLOG_ADMIN')) {
            $menu['announcements']->addChild('divider', array(
                'label' => '',
            ));
            $menu['announcements']['divider']->setAttributes(array(
                'role' => 'separator',
                'class' => 'divider',
            ));

            $menu['announcements']->addChild('post_category', array(
                'label' => 'Post Categories',
                'route' => 'post_category_index',
            ));
            $menu['announcements']->addChild('post_status', array(
                'label' => 'Post Statuses',
                'route' => 'post_status_index',
            ));
        }

        return $menu;
    }

    /**
     * Build a menu for blog pages.
     *
     * @param array $options
     * @return ItemInterface
     */
    public function pageNavMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav',
        ));
        $menu->setAttribute('dropdown', true);
        $pages = $this->em->getRepository('NinesBlogBundle:Page')->findBy(
                array('public' => true),
                array('weight' => 'ASC',
                    'title' => 'ASC')
        );

        $about = $menu->addChild('about', array(
            'uri' => '#',
            'label' => 'About' . self::CARET
        ));
        $about->setAttribute('dropdown', true);
        $about->setLinkAttribute('class', 'dropdown-toggle');
        $about->setLinkAttribute('data-toggle', 'dropdown');
        $about->setChildrenAttribute('class', 'dropdown-menu');

        foreach ($pages as $page) {
            $about->addChild($page->getTitle(), array(
                'route' => 'page_show',
                'routeParameters' => array(
                    'id' => $page->getId(),
                )
            ));
        }
        if ($this->hasRole('ROLE_BLOG_ADMIN')) {
            $divider = $about->addChild('divider', array(
                'label' => '',
            ));
            $divider->setAttributes(array(
                'role' => 'separator',
                'class' => 'divider',
            ));

            $about->addChild('page_admin', array(
                'label' => 'All Pages',
                'route' => 'page_index',
            ));
        }

        return $menu;
    }

}
