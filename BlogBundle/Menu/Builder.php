<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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
    public function postNavMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav',
        ]);
        $menu->setAttribute('dropdown', true);

        $status = $this->em->getRepository('NinesBlogBundle:PostStatus')->findOneBy([
            'public' => true,
        ]);
        $posts = $this->em->getRepository('NinesBlogBundle:Post')->findBy(
            ['status' => $status],
            ['id' => 'DESC']
        );
        $title = $options['title'] ?? 'Announcements';

        $menu->addChild('announcements', [
            'uri' => '#',
            'label' => $title,
        ]);
        $menu['announcements']->setAttribute('dropdown', true);
        $menu['announcements']->setLinkAttribute('class', 'dropdown-toggle');
        $menu['announcements']->setLinkAttribute('data-toggle', 'dropdown');
        $menu['announcements']->setChildrenAttribute('class', 'dropdown-menu');

        foreach ($posts as $post) {
            $menu['announcements']->addChild($post->getTitle(), [
                'route' => 'post_show',
                'routeParameters' => [
                    'id' => $post->getId(),
                ],
            ]);
        }
        $menu['announcements']->addChild('divider', [
            'label' => '',
        ]);
        $menu['announcements']['divider']->setAttributes([
            'role' => 'separator',
            'class' => 'divider',
        ]);

        $menu['announcements']->addChild('All Announcements', [
            'route' => 'nines_blog_post_index',
        ]);

        if ($this->hasRole('ROLE_BLOG_ADMIN')) {
            $menu['announcements']->addChild('divider', [
                'label' => '',
            ]);
            $menu['announcements']['divider']->setAttributes([
                'role' => 'separator',
                'class' => 'divider',
            ]);

            $menu['announcements']->addChild('nines_blog_post_category', [
                'label' => 'Post Categories',
                'route' => 'nines_blog_post_category_index',
            ]);
            $menu['announcements']->addChild('nines_blog_post_status', [
                'label' => 'Post Statuses',
                'route' => 'nines_blog_post_status_index',
            ]);
        }

        return $menu;
    }

    /**
     * Build a menu for blog pages.
     *
     * @return ItemInterface
     */
    public function pageNavMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav',
        ]);
        $menu->setAttribute('dropdown', true);
        $pages = $this->em->getRepository('NinesBlogBundle:Page')->findBy(
            ['public' => true, 'homepage' => false, 'inMenu' => true],
            ['weight' => 'ASC', 'title' => 'ASC']
        );

        $title = $options['title'] ?? 'About';
        $about = $menu->addChild('about', [
            'uri' => '#',
            'label' => $title,
        ]);
        $about->setAttribute('dropdown', true);
        $about->setLinkAttribute('class', 'dropdown-toggle');
        $about->setLinkAttribute('data-toggle', 'dropdown');
        $about->setChildrenAttribute('class', 'dropdown-menu');

        foreach ($pages as $page) {
            $about->addChild($page->getTitle(), [
                'route' => 'nines_blog_page_show',
                'routeParameters' => [
                    'id' => $page->getId(),
                ],
            ]);
        }
        if ($this->hasRole('ROLE_BLOG_ADMIN')) {
            $divider = $about->addChild('divider', [
                'label' => '',
            ]);
            $divider->setAttributes([
                'role' => 'separator',
                'class' => 'divider',
            ]);

            $about->addChild('nines_blog_page_admin', [
                'label' => 'All Pages',
                'route' => 'nines_blog_page_index',
            ]);
        }

        return $menu;
    }
}
