<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
 * @ORM\Table(name="nines_blog_post_status")
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostStatusRepository")
 */
class PostStatus extends AbstractTerm {
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $public = false;

    /**
     * @var Collection<int,Post>|Post[]
     * @ORM\OneToMany(targetEntity="Post", mappedBy="status")
     */
    private $posts;

    /**
     * Build the post.
     */
    public function __construct() {
        parent::__construct();
        $this->posts = new ArrayCollection();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPublic() : ?bool {
        return $this->public;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPublic(bool $public) : self {
        $this->public = $public;

        return $this;
    }

    /**
     * @return Collection<int,Post>|Post[]
     *
     * @codeCoverageIgnore
     */
    public function getPosts() : Collection {
        return $this->posts;
    }

    public function addPost(Post $post) : self {
        if ( ! $this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setStatus($this);
        }

        return $this;
    }

    public function removePost(Post $post) : self {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getStatus() === $this) {
                $post->setStatus(null);
            }
        }

        return $this;
    }
}
