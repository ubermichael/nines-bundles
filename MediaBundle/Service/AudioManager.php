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
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionClass;
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
class AudioManager extends AbstractFileManager implements EventSubscriber {
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

    private function uploadFile(Audio $audio) : void {
        $file = $audio->getAudioFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $audio->setOriginalName($file->getClientOriginalName());
        $filename = $this->upload($file);
        $path = $this->uploadDir . '/' . $filename;

        $audioFile = new File($path);
        $audio->setFileSize($audioFile->getSize());
        $audio->setAudioFile($audioFile);
        $audio->setAudioPath($filename);
        $audio->setMimeType($audioFile->getMimeType());
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $this->uploadFile($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args) : void {
        $entity = $args->getEntity();
        if ( ! $entity instanceof Audio) {
            return;
        }
        $fs = new Filesystem();
        try {
            $fs->remove($this->uploadDir . '/' . $entity->getAudioPath());
        } catch (IOExceptionInterface $e) {
            $this->logger->error("Cannot remote old file " . $this->uploadDir . '/' . $entity->getAudioPath());
        }
        $this->uploadFile($entity);
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $filePath = $this->uploadDir . '/' . $entity->getAudioPath();
            if (file_exists($filePath)) {
                $entity->setAudioFile(new File($filePath));
            } else {
                $this->logger->error("Cannot find audio file {$filePath}.");
            }
        }
        if ($entity instanceof AudioContainerInterface) {
            $repo = $this->em->getRepository(Audio::class);
            /** @var Audio[] $audios */
            $audios = $repo->findBy([
                'entity' => get_class($entity) . ':' . $entity->getId(),
            ]);
            $entity->setAudios($audios);
        }
    }

    public function postRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $fs = new Filesystem();

            try {
                $fs->remove($entity->getAudioFile());
            } catch (IOExceptionInterface $ex) {
                $this->logger->error("An error occured removing {$ex->getPath()}: {$ex->getMessage()}");
            }
        }
    }

    /**
     * Find the entity corresponding to a comment.
     */
    public function findEntity(Audio $audio) : ?object {
        list($class, $id) = explode(':', $audio->getEntity());
        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return null;
        }

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a audio refers to.
     */
    public function entityType(Audio $audio) : ?string {
        $entity = $this->findEntity($audio);
        if ( ! $entity) {
            return null;
        }

        $reflection = new ReflectionClass($entity);

        return $reflection->getShortName();
    }

    public function acceptsAudios(AbstractEntity $entity) : bool {
        return $entity instanceof AudioContainerInterface;
    }

    /**
     * Find the entity that the audio belongs to and generate a link to it.
     */
    public function linkToEntity(Audio $audio) : ?string {
        list($class, $id) = explode(':', $audio->getEntity());

        if ( ! isset($this->routing[$class])) {
            $this->logger->error('No routing information for ' . $class);

            return null;
        }

        return $this->router->generate($this->routing[$class], ['id' => $id]);
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
     * @required
     */
    public function setRouter(UrlGeneratorInterface $router) : void {
        $this->router = $router;
    }
}
