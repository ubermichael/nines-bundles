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

    private function uploadFile(Audio $audio) : void {
        $file = $audio->getFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $audio->setOriginalName($file->getClientOriginalName());
        $filename = $this->upload($file);
        $path = $this->uploadDir . '/' . $filename;

        $audioFile = new File($path);
        $audio->setFileSize($audioFile->getSize());
        $audio->setFile($audioFile);
        $audio->setPath($filename);
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
            $fs->remove($this->uploadDir . '/' . $entity->getPath());
        } catch (IOExceptionInterface $e) {
            $this->logger->error("Cannot remote old file " . $this->uploadDir . '/' . $entity->getAudioPath());
        }
        $this->uploadFile($entity);
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Audio) {
            $filePath = $this->uploadDir . '/' . $entity->getPath();
            if (file_exists($filePath)) {
                $entity->setFile(new File($filePath));
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
                $fs->remove($entity->getFile());
            } catch (IOExceptionInterface $ex) {
                $this->logger->error("An error occured removing {$ex->getPath()}: {$ex->getMessage()}");
            }
        }
    }

    public function acceptsAudios(AbstractEntity $entity) : bool {
        return $entity instanceof AudioContainerInterface;
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

}
