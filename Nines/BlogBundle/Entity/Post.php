<?php

namespace Nines\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * A blog post.
 *
 * @ORM\Table(name="blog_post", indexes={
 *   @ORM\Index(name="blog_post_content", columns={"title","searchable"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Post extends AbstractEntity {

    /**
     * Blog post title.
     *
     * @var string
     * 
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private $title;

    /**
     * An excerpt, to display in lists.
     *
     * @var string
     * 
     * @ORM\Column(name="excerpt", type="text", nullable=true)
     */
    private $excerpt;

    /**
     * The content of the post, as HTML.
     *
     * @var string
     * 
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

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

    /**
     * Return the title of the post.
     * 
     * @return type
     */
    public function __toString() {
        return $this->title;
    }

    /**
     * Set title
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
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Post
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Set category
     *
     * @param PostCategory $category
     *
     * @return Post
     */
    public function setCategory(PostCategory $category = null) {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return PostCategory
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Set status
     *
     * @param PostStatus $status
     *
     * @return Post
     */
    public function setStatus(PostStatus $status = null) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return PostStatus
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Post
     */
    public function setUser(User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set excerpt
     *
     * @param string $excerpt
     *
     * @return Post
     */
    public function setExcerpt($excerpt) {
        $this->excerpt = $excerpt;

        return $this;
    }

    /**
     * Get excerpt
     *
     * @return string
     */
    public function getExcerpt() {
        return $this->excerpt;
    }


    /**
     * Set searchable
     *
     * @param string $searchable
     *
     * @return Post
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Get searchable
     *
     * @return string
     */
    public function getSearchable()
    {
        return $this->searchable;
    }
}
