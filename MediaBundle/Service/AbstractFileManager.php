<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\UtilBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * File management base class
 *
 * @author Michael Joyce <ubermichael@gmail.com>
 */
abstract class AbstractFileManager {
    public const FORBIDDEN = '/[^a-z0-9_. -]/i';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $uploadDir;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct($root) {
        $this->root = $root;
    }

    public static function getMaxUploadSize($asBytes = true) {
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
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];
        $exp = floor(log($maxBytes, 1024));
        $est = round($maxBytes / 1024 ** $exp, 1);

        return $est . $units[$exp];
    }

    public static function bytesToSize($bytes) {
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];
        $exp = floor(log($bytes, 1024));
        $est = round($bytes / 1024 ** $exp, 1);

        return $est . $units[$exp];
    }

    public static function parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $bytes = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($bytes * 1024 ** mb_stripos('bkmgtpezy', $unit[0]));
        }

        return round($bytes);
    }

    public function setUploadDir($dir) : void {
        if ('/' !== $dir[0]) {
            $this->uploadDir = $this->root . '/' . $dir;
        } else {
            $this->uploadDir = $dir;
        }
    }

    /**
     * @return string
     */
    public function getUploadDir() {
        return $this->uploadDir;
    }

    public function upload(UploadedFile $file) {
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = implode('.', [
            preg_replace(self::FORBIDDEN, '_', $basename),
            uniqid(),
            $file->guessExtension(),
        ]);
        if ( ! file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        $file->move($this->uploadDir, $filename);

        return $filename;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

}
