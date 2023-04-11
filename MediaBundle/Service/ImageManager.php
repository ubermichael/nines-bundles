<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image Manager service that handles file uploads, thumbnailing, and database
 * stuff.
 */
class ImageManager extends AbstractFileManager implements EventSubscriber {
    private ?Thumbnailer $thumbnailer = null;

    private ?ImageRepository $repo = null;

    /**
     * Store the image file, extracta  little metadata, and generate a thumbnail.
     *
     * @throws Exception
     */
    protected function uploadFile(Image $image) : void {
        $file = $image->getFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $image->setOriginalName($file->getClientOriginalName());

        $filename = $this->upload($file);
        $path = $this->uploadDir . '/' . $filename;

        $imageFile = new File($path);
        $image->setPath($filename);
        $image->setFile($imageFile);
        $image->setFileSize($imageFile->getSize());
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
            $filePath = $this->uploadDir . '/' . $entity->getPath();
            if (file_exists($filePath)) {
                $entity->setFile(new File($filePath));
            } else {
                $this->logger->error('Cannot find image file ' . $this->uploadDir . '/' . $entity->getPath());
            }
            $thumbPath = $this->uploadDir . '/' . $entity->getThumbPath();
            if (file_exists($thumbPath)) {
                $entity->setThumbFile(new File($thumbPath));
            } else {
                $this->logger->error('Cannot find thumbnail file ' . $this->uploadDir . '/' . $entity->getPath());
            }
        }
        if ($entity instanceof ImageContainerInterface) {
            $images = $this->repo->findBy([
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
        if ($entity instanceof Image && $entity->getFile()) {
            $fs = new Filesystem();

            try {
                $this->remove($entity->getFile());
                $this->remove($entity->getThumbFile());
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

    public function acceptsImages(AbstractEntity $entity) : bool {
        return $entity instanceof ImageContainerInterface;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setThumbnailer(Thumbnailer $thumbnailer) : void {
        $this->thumbnailer = $thumbnailer;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setRepo(ImageRepository $repo) : void {
        $this->repo = $repo;
    }
}
