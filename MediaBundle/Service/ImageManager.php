<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\UtilBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of FileUploader.
 *
 * @author Michael Joyce <ubermichael@gmail.com>
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

    public function getSubscribedEvents() {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad,
            Events::postRemove,
        ];
    }

    /**
     * Find the entity corresponding to a comment.
     *
     * @return mixed
     */
    public function findEntity(Image $image) {
        [$class, $id] = explode(':', $image->getEntity());
        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return;
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

        try {
            $reflection = new ReflectionClass($entity);
        } catch (ReflectionException $e) {
            $this->logger->error('Cannot find entity for image ' . $image->getEntity());

            return null;
        }

        return $reflection->getShortName();
    }

    public function acceptsImages(AbstractEntity $entity) : bool {
        return $entity instanceof ImageContainerInterface;
    }

    /**
     * Find the links for an entity.
     *
     * @param mixed $entity
     *
     * @return Collection|Link[]
     */
    public function findLinks(ImageContainerInterface $entity) {
        $class = ClassUtils::getClass($entity);

        return $this->linkRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    public function setLinks(ImageContainerInterface $entity, $images) : void {
        foreach ($entity->getImages() as $image) {
            if ($this->em->contains($image)) {
                $this->em->remove($image);
            }
        }
        $entity->setLinks($images);

        foreach ($images as $image) {
            $this->em->persist($image);
        }
    }

    public function linkToEntity(Image $image) {
        [$class, $id] = explode(':', $image->getEntity());

        if ( ! isset($this->routing[$class])) {
            $this->logger->error('No routing information for ' . $class);
            return '';
        }

        return $this->router->generate($this->routing[$class], ['id' => $id]);
    }

    /**
     * @required
     */
    public function setRouter(UrlGeneratorInterface $router) : void {
        $this->router = $router;
    }

}
