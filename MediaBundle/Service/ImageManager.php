<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionClass;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Image Manager service that handles file uploads, thumbnailing, and database
 * stuff.
 */
class ImageManager extends AbstractFileManager implements EventSubscriber {
    /**
     * @var Thumbnailer
     */
    private $thumbnailer;

    /**
     * @var array
     */
    private $routing;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct($root, $routing) {
        parent::__construct($root);
        $this->routing = $routing;
    }

    /**
     * Store the image file, extracta  little metadata, and generate a thumbnail.
     *
     * @throws Exception
     */
    protected function uploadFile(Image $image) : void {
        $file = $image->getImageFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $image->setOriginalName($file->getClientOriginalName());

        $filename = $this->upload($file);
        $path = $this->uploadDir . '/' . $filename;

        $imageFile = new File($path);
        $image->setImagePath($filename);
        $image->setImageFile($imageFile);
        $image->setImageSize($imageFile->getSize());
        $image->setMimeType($imageFile->getMimeType());
        $dimensions = getimagesize($path);
        $image->setImageWidth($dimensions[0]);
        $image->setImageHeight($dimensions[1]);
        $thumbPath = $this->thumbnailer->thumbnail($image);
        $image->setThumbPath($thumbPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() : array {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad,
            Events::postRemove,
        ];
    }

    /**
     * Event subscriber action, called before saving an image to the database.
     *
     * @throws Exception
     */
    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $this->uploadFile($entity);
        }
    }

    /**
     * Event subscriber action, called before updating an image in the database.
     *
     * @throws Exception
     */
    public function preUpdate(PreUpdateEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $this->uploadFile($entity);
        }
    }

    /**
     * Event subscriber action. After loading an image entity from the database,
     * add the file object to the entity.
     */
    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $filePath = $this->uploadDir . '/' . $entity->getImagePath();
            if (file_exists($filePath)) {
                $entity->setImageFile(new File($filePath));
            } else {
                $this->logger->error('Cannot find image file ' . $this->uploadDir . '/' . $entity->getImagePath());
            }
            $thumbPath = $this->uploadDir . '/' . $entity->getThumbPath();
            if (file_exists($thumbPath)) {
                $entity->setThumbFile(new File($thumbPath));
            } else {
                $this->logger->error('Cannot find thumbnail file ' . $this->uploadDir . '/' . $entity->getImagePath());
            }
        }
        if ($entity instanceof ImageContainerInterface) {
            $repo = $this->em->getRepository(Image::class);
            /** @var Image[] $images */
            $images = $repo->findBy([
                'entity' => get_class($entity) . ':' . $entity->getId(),
            ]);
            $entity->setImages($images);
        }
    }

    /**
     * Event subscriber action. After removing an image entity from the database
     * remove the image and thumbnail files.'.
     */
    public function postRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $fs = new Filesystem();

            try {
                $fs->remove($entity->getImageFile());
                $fs->remove($entity->getThumbFile());
            } catch (IOExceptionInterface $ex) {
                $this->logger->error("An error occurred removing {$ex->getPath()}: {$ex->getMessage()}");
            }
        }
        if ($entity instanceof ImageContainerInterface) {
            foreach ($entity->getImages() as $image) {
                $this->em->remove($image);
            }
            $this->em->flush();
        }
    }

    /**
     * Find the entity corresponding to a comment.
     */
    public function findEntity(Image $image) : ?object {
        list($class, $id) = explode(':', $image->getEntity());
        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return null;
        }

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a image refers to.
     */
    public function entityType(Image $image) : ?string {
        $entity = $this->findEntity($image);
        if ( ! $entity) {
            return null;
        }

        $reflection = new ReflectionClass($entity);

        return $reflection->getShortName();
    }

    public function acceptsImages(AbstractEntity $entity) : bool {
        return $entity instanceof ImageContainerInterface;
    }

    /**
     * Find the entity that the image belongs to and generate a link to it.
     */
    public function linkToEntity(Image $image) : ?string {
        list($class, $id) = explode(':', $image->getEntity());

        if ( ! isset($this->routing[$class])) {
            $this->logger->error('No routing information for ' . $class);

            return null;
        }

        return $this->router->generate($this->routing[$class], ['id' => $id]);
    }

    /**
     * @required
     */
    public function setThumbnailer(Thumbnailer $thumbnailer) : void {
        $this->thumbnailer = $thumbnailer;
    }

    /**
     * @required
     */
    public function setRouter(UrlGeneratorInterface $router) : void {
        $this->router = $router;
    }
}
