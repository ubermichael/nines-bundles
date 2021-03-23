<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
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
 * @ORM\Table(name="blog_post", indexes={
 *     @ORM\Index(name="blog_post_content", columns={"title", "searchable"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Post extends AbstractEntity implements ContentEntityInterface
{
    use ContentExcerptTrait;

    /**
     * Blog post title.
     *
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="include_comments", type="boolean", nullable=false)
     */
    private $includeComments;

    /**
     * Searchable version of the content, with the tags stripped out.
     *
     * @var string
     *
     * @ORM\Column(name="searchable", type="text", nullable=false)
     */
    private $searchable;

    /**
     * Post category.
     *
     * @var PostCategory
     *
     * @ORM\ManyToOne(targetEntity="PostCategory", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * Post status.
     *
     * @var PostStatus
     *
     * @ORM\ManyToOne(targetEntity="PostStatus", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * User that created the post.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="Nines\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct() {
        parent::__construct();
        $this->includeComments = false;
    }

    /**
     * Return the title of the post.
     */
    public function __toString() : string {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Post
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set category.
     *
     * @param PostCategory $category
     *
     * @return Post
     */
    public function setCategory(?PostCategory $category = null) {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return PostCategory
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Set status.
     *
     * @param PostStatus $status
     *
     * @return Post
     */
    public function setStatus(?PostStatus $status = null) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return PostStatus
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Post
     */
    public function setUser(?User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set searchable.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setSearchable() {
        $this->searchable = strip_tags($this->content);

        return $this;
    }

    /**
     * Get searchable.
     *
     * @return string
     */
    public function getSearchable() {
        return $this->searchable;
    }

    /**
     * Set includeComments.
     *
     * @param bool $includeComments
     *
     * @return Post
     */
    public function setIncludeComments($includeComments) {
        $this->includeComments = $includeComments;

        return $this;
    }

    /**
     * Get includeComments.
     *
     * @return bool
     */
    public function getIncludeComments() {
        return $this->includeComments;
    }
}
