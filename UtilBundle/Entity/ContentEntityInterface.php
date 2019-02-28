<?php

namespace Nines\UtilBundle\Entity;


interface ContentEntityInterface
{
    public function setExcerpt($excerpt);

    public function getExcerpt();

    public function setContent($content);

    public function getContent();

}
