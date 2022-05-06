<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Menu;

use Knp\Menu\ItemInterface;
use Nines\BlogBundle\Repository\PageRepository;
use Nines\BlogBundle\Repository\PostRepository;
use Nines\BlogBundle\Repository\PostStatusRepository;
use Nines\UtilBundle\Menu\AbstractBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class to build some menus for navigation.
 */
class Builder extends AbstractBuilder {
    use ContainerAwareTrait;

    private ?PostStatusRepository $postStatusRepository = null;

    private ?PostRepository $postRepository = null;

    private ?PageRepository $pageRepository = null;

    /**
     * @param array<string,string> $options
     */
    public function postMenu(array $options) : ItemInterface {
        $menu = $this->dropdown($options['title'] ?? 'Announcements');

        $public = $this->postStatusRepository->findBy(['public' => true]);

        // @TODO turn this into menuQuery() or something.
        $posts = $this->postRepository->findBy(
            ['status' => $public],
            ['id' => 'DESC'],
            2,
        );
        foreach ($posts as $post) {
            $menu->addChild($post->getTitle(), [
                'route' => 'nines_blog_post_show',
                'routeParameters' => [
                    'id' => $post->getId(),
                ],
            ]);
        }
        $this->addDivider($menu);

        $menu->addChild('All Announcements', [
            'route' => 'nines_blog_post_index',
        ]);

        if ($this->hasRole('ROLE_BLOG_ADMIN')) {
            $this->addDivider($menu, 'divider2');

            $menu->addChild('Post Categories', [
                'route' => 'nines_blog_post_category_index',
            ]);
            $menu->addChild('Post Statuses', [
                'route' => 'nines_blog_post_status_index',
            ]);
        }

        return $menu->getParent();
    }

    /**
     * @param array<string,string> $options
     */
    public function pageMenu(array $options) : ItemInterface {
        $menu = $this->dropdown($options['title'] ?? 'About');

        // @TODO turn this into menuQuery().
        $pages = $this->pageRepository->findBy(
            ['public' => true, 'homepage' => false, 'inMenu' => true],
            ['weight' => 'ASC', 'title' => 'ASC'],
        );
        foreach ($pages as $page) {
            $menu->addChild($page->getTitle(), [
                'route' => 'nines_blog_page_show',
                'routeParameters' => [
                    'id' => $page->getId(),
                ],
            ]);
        }

        if ($this->hasRole('ROLE_BLOG_ADMIN')) {
            $this->addDivider($menu);
            $menu->addChild('All Pages', [
                'route' => 'nines_blog_page_index',
            ]);
        }

        return $menu->getParent();
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setPostStatusRepository(PostStatusRepository $postStatusRepository) : void {
        $this->postStatusRepository = $postStatusRepository;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setPostRepository(PostRepository $postRepository) : void {
        $this->postRepository = $postRepository;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setPageRepository(PageRepository $pageRepository) : void {
        $this->pageRepository = $pageRepository;
    }
}
