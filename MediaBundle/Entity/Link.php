<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Nines\MediaBundle\Repository\LinkRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"url", "text"}, flags={"fulltext"}),
 *     @ORM\Index(columns={"entity"})
 * })
 */
class Link extends AbstractEntity {
    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $entity;

    /**
     * @var string
     * @Assert\Url
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $text;

    public function __construct() {
        parent::__construct();
    }

    public function __toString() : string {
        if ($this->text) {
            return "<a href='{$this->url}'>{$this->text}</a>";
        }
        $host = parse_url($this->url, PHP_URL_HOST);

        return "<a href='{$this->url}'>{$host}</a>";
    }

    public function setEntity(AbstractEntity $entity) : void {
        if ( ! $entity->getId()) {
            throw new Exception('Link entities must be persisted.');
        }
        $this->entity = ClassUtils::getClass($entity) . ':' . $entity->getId();
    }

    public function getUrl() : ?string {
        return $this->url;
    }

    public function setUrl(string $url) : self {
        $this->url = $url;

        return $this;
    }

    public function getText() : ?string {
        return $this->text;
    }

    public function setText(?string $text) : self {
        $this->text = preg_replace('/(^\p{Z})|(\p{Z}$)/u', '', $text);

        return $this;
    }

    public function getScheme() {
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    public function getHost() {
        return parse_url($this->url, PHP_URL_HOST);
    }

    public function getPort() {
        return parse_url($this->url, PHP_URL_PORT);
    }

    public function getUser() {
        return parse_url($this->url, PHP_URL_USER);
    }

    public function getPass() {
        return parse_url($this->url, PHP_URL_PASS);
    }

    public function getPath() {
        return parse_url($this->url, PHP_URL_PATH);
    }

    public function getQuery() {
        return parse_url($this->url, PHP_URL_QUERY);
    }

    public function getFragment() {
        return parse_url($this->url, PHP_URL_FRAGMENT);
    }

    public function getEntity() : ?string {
        return $this->entity;
    }
}
