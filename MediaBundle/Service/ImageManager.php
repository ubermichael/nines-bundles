<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of FileUploader.
 *
 * @author Michael Joyce <ubermichael@gmail.com>
 */
class ImageManager extends AbstractFileManager {
    /**
     * @var Thumbnailer
     */
    private $thumbnailer;

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

    public function findEntity(Image $image) {
        list($class, $id) = explode(':', $image->getEntity());

        return $this->em->find($class, $id);
    }

    /**
     * @required
     */
    public function setThumbnailer(Thumbnailer $thumbnailer) : void {
        $this->thumbnailer = $thumbnailer;
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $this->uploadFile($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $this->uploadFile($entity);
        }
        if ($entity instanceof ImageContainerInterface) {
        }
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $filePath = $this->uploadDir . '/' . $entity->getImagePath();
            if (file_exists($filePath)) {
                $entity->setImageFile(new File($filePath));
            }
            $thumbPath = $this->uploadDir . '/' . $entity->getThumbPath();
            if (file_exists($thumbPath)) {
                $entity->setThumbFile(new File($thumbPath));
            }
        }
        if ($entity instanceof ImageContainerInterface) {
            $repo = $this->em->getRepository(Image::class);
            $images = $repo->findBy([
                'entity' => get_class($entity) . ':' . $entity->getId(),
            ]);
            $entity->setImages($images);
        }
    }

    public function postRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Image) {
            $fs = new Filesystem();

            try {
                $fs->remove($entity->getImageFile());
            } catch (IOExceptionInterface $ex) {
                $this->logger->error("An error occured removing {$ex->getPath()}: {$ex->getMessage()}");
            }
        }
        if ($entity instanceof ImageContainerInterface) {
            foreach ($entity->getImages() as $image) {
                $this->em->remove($image);
            }
            $this->em->flush();
        }
    }
}
