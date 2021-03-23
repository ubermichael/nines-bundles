<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * PostCategory.
 *
 * @ORM\Table(name="blog_post_category")
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostCategoryRepository")
 */
class PostCategory extends AbstractTerm
{
    /**
     * Posts in the category.
     *
     * @var Collection|Post[]
     * @ORM\OneToMany(targetEntity="Post", mappedBy="category")
     */
    private $posts;

    /**
     * Construct the category.
     */
    public function __construct() {
        parent::__construct();
        $this->posts = new ArrayCollection();
    }

    /**
     * Add post.
     *
     * @return PostCategory
     */
    public function addPost(Post $post) {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post.
     */
    public function removePost(Post $post) : void {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts.
     *
     * @return Collection
     */
    public function getPosts() {
        return $this->posts;
    }
}
