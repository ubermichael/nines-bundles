<?php

namespace Nines\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Page
 *
 * @ORM\Table(name="blog_page", indexes={
 *   @ORM\Index(name="blog_page_content", columns={"title","searchable"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page extends AbstractEntity {

    /**
     * Heavier weighted pages will sort to the bottom.
     *
     * @var int
     * @ORM\Column(name="weight", type="integer", nullable=false)
     */
    private $weight;

    /**
     * True if the page is public. Defaults to false.
     *
     * @var boolean
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;

    /**
     * @var boolean
     * @ORM\Column(name="include_comments", type="boolean", nullable=false)
     */
    private $includeComments;

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
     * User that created the post.
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="Nines\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Build a page.
     */
    public function __construct() {
        parent::__construct();
        $this->weight = 0;
        $this->public = false;
        $this->includeComments = false;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Page
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Page
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Page
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set searchable
     *
     * @param string $searchable
     *
     * @return Page
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

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Page
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function __toString() {
        return $this->title;
    }


    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Page
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set excerpt
     *
     * @param string $excerpt
     *
     * @return Page
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    /**
     * Get excerpt
     *
     * @return string
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * Set includeComments
     *
     * @param boolean $includeComments
     *
     * @return Page
     */
    public function setIncludeComments($includeComments)
    {
        $this->includeComments = $includeComments;

        return $this;
    }

    /**
     * Get includeComments
     *
     * @return boolean
     */
    public function getIncludeComments()
    {
        return $this->includeComments;
    }
}
