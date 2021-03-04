<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * PostStatus.
 *
 * @ORM\Table(name="blog_post_status")
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostStatusRepository")
 */
class PostStatus extends AbstractTerm {
    /**
     * True if the status is meant to be public.
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $public;

    /**
     * List of the posts with this status.
     *
     * @var Collection|Post[]
     * @ORM\OneToMany(targetEntity="Post", mappedBy="status")
     */
    private $posts;

    /**
     * Build the post.
     */
    public function __construct() {
        parent::__construct();
        $this->public = false;
        $this->posts = new ArrayCollection();
    }

    /**
     * Add post.
     *
     * @return PostStatus
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

    /**
     * Set public.
     *
     * @param bool $public
     *
     * @return PostStatus
     */
    public function setPublic($public) {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public.
     *
     * @return bool
     */
    public function getPublic() {
        return $this->public;
    }
}
