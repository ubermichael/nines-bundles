<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\AudioRepository;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=AudioRepository::class)
 */
class Audio extends AbstractEntity implements EntityReferenceInterface, StoredFileInterface {
    use EntityReferenceTrait;

    use StoredFileTrait;

    public function __construct() {
        parent::__construct();
    }
}
