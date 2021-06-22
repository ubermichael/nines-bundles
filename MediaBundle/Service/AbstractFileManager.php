<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\EntityReferenceInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * File management base class.
 *
 * @author Michael Joyce <ubermichael@gmail.com>
 */
abstract class AbstractFileManager {
    /**
     * Regular expression that matches all forbidden characters. The only
     * allowed characters in a file name are alphanumerics, underscore, dot,
     * space, and dash. All other characters are removed form file names.
     */
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

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * Mapping of class name to route name.
     *
     * @var array
     */
    private $routing;

    public function __construct($root, $routing) {
        $this->root = $root;
        $this->routing = $routing;
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
     * @param int|float $bytes
     */
    public static function bytesToSize($bytes) : string {
        $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];
        $exp = floor(log($bytes, 1024));
        $est = round($bytes / 1024 ** $exp, 1);

        return $est . $units[$exp];
    }

    /**
     * Parse a string (eg. 9.2kb) into a number of bytes (9420)
     */
    public static function parseSize(string $size) : float {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $bytes = (float)preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
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

    public function getUploadDir() : string {
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
     * Find the entity corresponding to a reference.
     */
    public function findEntity(EntityReferenceInterface $reference) : ?object {
        list($class, $id) = explode(':', $reference->getEntity());
        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return null;
        }

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a audio refers to.
     */
    public function entityType(EntityReferenceInterface $reference) : ?string {
        $entity = $this->findEntity($reference);
        if ( ! $entity) {
            return null;
        }

        $reflection = new ReflectionClass($entity);

        return $reflection->getShortName();
    }

    /**
     * Find the entity that the audio belongs to and generate a link to it.
     */
    public function linkToEntity(EntityReferenceInterface $reference) : ?string {
        list($class, $id) = explode(':', $reference->getEntity());

        if ( ! isset($this->routing[$class])) {
            $this->logger->error('No routing information for ' . $class);

            return null;
        }

        return $this->router->generate($this->routing[$class], ['id' => $id]);
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
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    /**
     * @required
     */
    public function setRouter(UrlGeneratorInterface $router) : void {
        $this->router = $router;
    }
}
