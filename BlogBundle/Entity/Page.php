<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
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
 * Page.
 *
 * @ORM\Table(name="blog_page", indexes={
 *   @ORM\Index(name="blog_page_content", columns={"title","searchable"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="Nines\BlogBundle\Repository\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page extends AbstractEntity implements ContentEntityInterface {
    use ContentExcerptTrait;

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
     * @var bool
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;

    /**
     * True if this is the home page. There can only be one.
     *
     * @var bool
     * @ORM\Column(name="homepage", type="boolean", options={"default": 0})
     */
    private $homepage;

    /**
     * @var bool
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
        $this->homepage = false;
        $this->includeComments = false;
    }

    public function __toString() {
        return $this->title;
    }

    /**
     * Set public.
     *
     * @param bool $public
     *
     * @return Page
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

    /**
     * Set homepage.
     *
     * @param bool $homepage
     *
     * @return Page
     */
    public function setHomepage($homepage) {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * Get homepage.
     *
     * @return bool
     */
    public function getHomepage() {
        return $this->homepage;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Page
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
     * Set searchable.
     *
     * @param string $searchable
     *
     * @return Page
     */
    public function setSearchable($searchable) {
        $this->searchable = $searchable;

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
     * Set user.
     *
     * @return Page
     */
    public function setUser(User $user) {
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
     * Set weight.
     *
     * @param int $weight
     *
     * @return Page
     */
    public function setWeight($weight) {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight.
     *
     * @return int
     */
    public function getWeight() {
        return $this->weight;
    }

    /**
     * Set includeComments.
     *
     * @param bool $includeComments
     *
     * @return Page
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
