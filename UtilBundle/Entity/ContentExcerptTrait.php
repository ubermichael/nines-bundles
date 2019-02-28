<?php

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
     * Set excerpt
     *
     * @param string $excerpt
     *
     * @return ArtisticStatement
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
     * Set content
     *
     * @param string $content
     *
     * @return ArtisticStatement
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

}
