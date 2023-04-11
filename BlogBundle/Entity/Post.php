<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\ContentEntityInterface;
use Nines\UtilBundle\Entity\ContentExcerptTrait;

/**
 * A blog post.
 *
 * @ORM\Table(name="nines_blog_post", indexes={
 *     @ORM\Index(name="blog_post_ft", columns={"title", "searchable"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Post extends AbstractEntity implements ContentEntityInterface {
    use ContentExcerptTrait;

    /**
     * @ORM\Column(name="include_comments", type="boolean", nullable=false)
     */
    private bool $includeComments = false;

    /**
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private ?string $title = null;

    /**
     * @ORM\Column(name="searchable", type="text", nullable=false)
     */
    private ?string $searchable = null;

    /**
     * @ORM\ManyToOne(targetEntity="PostCategory", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?PostCategory $category = null;

    /**
     * @ORM\ManyToOne(targetEntity="PostStatus", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?PostStatus $status = null;

    /**
     * @ORM\ManyToOne(targetEntity="Nines\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Return the title of the post.
     */
    public function __toString() : string {
        return $this->title;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getIncludeComments() : ?bool {
        return $this->includeComments;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setIncludeComments(bool $includeComments) : self {
        $this->includeComments = $includeComments;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTitle() : ?string {
        return $this->title;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setTitle(string $title) : self {
        $this->title = $title;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSearchable() : ?string {
        return $this->searchable;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setSearchable() : self {
        if ($this->content) {
            $this->searchable = strip_tags($this->content);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getCategory() : ?PostCategory {
        return $this->category;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setCategory(?PostCategory $category) : self {
        $this->category = $category;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getStatus() : ?PostStatus {
        return $this->status;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setStatus(?PostStatus $status) : self {
        $this->status = $status;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getUser() : ?User {
        return $this->user;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setUser(?User $user) : self {
        $this->user = $user;

        return $this;
    }
}
