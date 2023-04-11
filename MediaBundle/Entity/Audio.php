<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\AudioRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\LinkedEntityInterface;
use Nines\UtilBundle\Entity\LinkedEntityTrait;

/**
 * @ORM\Entity(repositoryClass=AudioRepository::class)
 * @ORM\Table(name="nines_media_audio", indexes={
 *     @ORM\Index(name="nines_media_audio_ft", columns={"original_name", "description"}, flags={"fulltext"}),
 *     @ORM\Index(columns={"entity"})
 * })
 */
class Audio extends AbstractEntity implements LinkedEntityInterface, StoredFileInterface {
    use LinkedEntityTrait;

    use StoredFileTrait;

    public function __construct() {
        parent::__construct();
    }
}
