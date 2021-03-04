<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

interface ContentEntityInterface {
    /**
     * Set the excerpt for an entity.
     *
     * @param $excerpt
     *
     * @return $this
     */
    public function setExcerpt($excerpt);

    /**
     * Get the excerpt for an entity.
     */
    public function getExcerpt() : string;

    /**
     * Set the content of an entity.
     *
     * @param $content
     *
     * @return $this
     */
    public function setContent($content);

    /**
     * Get the content from an entity.
     */
    public function getContent() : string;
}
