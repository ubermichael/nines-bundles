<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File management base class.
 */
abstract class AbstractFileManager {
    /**
     * Regular expression that matches all forbidden characters. The only
     * allowed characters in a file name are alphanumerics, underscore, dot,
     * space, and dash. All other characters are removed form file names.
     */
    public const FORBIDDEN = '/[^a-z0-9_. -]/i';

    protected ?LoggerInterface $logger = null;

    protected ?string $root = null;

    protected ?string $uploadDir = null;

    protected ?EntityManagerInterface $em = null;

    private bool $copy = false;

    private bool $remove = true;

    public function __construct(string $root) {
        $this->root = $root;
    }

    /**
     * Get the maximum file upload size from PHP's configuration. This may
     * be inaccurate, depending on the webserver configuration.
     *
     * @return float|int|string
     */
    public static function getMaxUploadSize(bool $asBytes = true) {
        static $maxBytes = -1;

        if ($maxBytes < 0) {
            $postMax = self::parseSize(ini_get('post_max_size'));
            if ($postMax > 0) {
                $maxBytes = $postMax;
            }

            $uploadMax = self::parseSize(ini_get('upload_max_filesize'));
            if ($uploadMax > 0 && $uploadMax < $maxBytes) {
                $maxBytes = $uploadMax;
            }
        }
        if ($asBytes) {
            return $maxBytes;
        }

        return self::bytesToSize($maxBytes);
    }

    /**
     * Convert a raw byte count into a readable number.
     *
     * @param float|int $bytes
     */
    public static function bytesToSize($bytes) : string {
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];
        $exp = floor(log($bytes, 1024));
        $est = round($bytes / 1024 ** $exp, 1);

        return $est . $units[$exp];
    }

    /**
     * Parse a string (eg. 9.2kb) into a number of bytes (9420).
     */
    public static function parseSize(string $size) : float {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $bytes = (float) preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($bytes * 1024 ** mb_stripos('bkmgtpezy', $unit[0]));
        }

        return round($bytes);
    }

    public function setUploadDir(string $dir) : void {
        if ('/' !== $dir[0]) {
            $this->uploadDir = $this->root . '/' . $dir;
        } else {
            $this->uploadDir = $dir;
        }
    }

    public function getUploadDir() : string {
        return $this->uploadDir;
    }

    public function upload(UploadedFile $file) : string {
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = implode('.', [
            preg_replace(self::FORBIDDEN, '_', $basename),
            uniqid(),
            $file->guessExtension(),
        ]);
        if ( ! file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        if ($this->copy) {
            copy($file->getPathname(), $this->uploadDir . '/' . $filename);
        } else {
            $file->move($this->uploadDir, $filename);
        }

        return $filename;
    }

    protected function remove(File $file) : void {
        // In a test environment we don't want to actually remove the files
        if ($this->remove) {
            $fs = new Filesystem();
            $fs->remove($file->getRealPath());
        }
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    public function setCopy(bool $copy) : void {
        $this->copy = $copy;
    }

    public function setEnv(string $env) : void {
        if ('test' === $env) {
            $this->remove = false;
        }
    }
}
