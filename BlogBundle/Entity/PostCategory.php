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
 * PostCategory.
 *
 * @ORM\Table(name="nines_blog_post_category")
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostCategoryRepository")
 */
class PostCategory extends AbstractTerm {
    /**
     * Posts in the category.
     *
     * @var Collection<int,Post>|Post[]
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
            $post->setCategory($this);
        }

        return $this;
    }

    public function removePost(Post $post) : self {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }

        return $this;
    }
}
