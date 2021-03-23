<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

trait ContentExcerptTrait
{
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $excerpt;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * Set excerpt.
     *
     * @param string $excerpt
     */
    public function setExcerpt($excerpt) : self {
        $this->excerpt = $excerpt;

        return $this;
    }

    /**
     * Get excerpt.
     */
    public function getExcerpt() : string {
        if ( ! $this->excerpt) {
            return '';
        }

        return $this->excerpt;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content) : self {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     */
    public function getContent() : string {
        if ( ! $this->content) {
            return '';
        }

        return $this->content;
    }
}
