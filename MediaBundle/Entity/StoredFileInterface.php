<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Stringable;
use Symfony\Component\HttpFoundation\File\File;

interface StoredFileInterface extends Stringable {
    public function getFile() : ?File;

    public function setFile(?File $file) : self;

    public function getExtension() : ?string;

    public function getPublic() : ?bool;

    public function setPublic(bool $public) : self;

    public function getOriginalName() : ?string;

    public function setOriginalName(?string $originalName) : self;

    public function getPath() : ?string;

    public function setPath(?string $path) : self;

    public function getMimeType() : ?string;

    public function setMimeType(?string $mimeType) : self;

    public function getFileSize() : ?int;

    public function setFileSize(?int $fileSize) : self;

    public function getDescription() : ?string;

    public function setDescription(?string $description) : self;

    public function getLicense() : ?string;

    public function setLicense(?string $license) : self;
}
