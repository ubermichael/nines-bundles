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
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Entity\PdfContainerInterface;
use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionClass;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Pdf Manager service that handles file uploads, thumbnailing, and database
 * stuff.
 */
class PdfManager extends AbstractFileManager implements EventSubscriber {
    /**
     * @var Thumbnailer
     */
    private $thumbnailer;

    /**
     * Store the pdf file, extracta  little metadata, and generate a thumbnail.
     *
     * @throws Exception
     */
    protected function uploadFile(Pdf $pdf) : void {
        $file = $pdf->getFile();
        if ( ! $file instanceof UploadedFile) {
            return;
        }
        $pdf->setOriginalName($file->getClientOriginalName());

        $filename = $this->upload($file);
        $path = $this->uploadDir . '/' . $filename;

        $pdfFile = new File($path);
        $pdf->setPath($filename);
        $pdf->setFile($pdfFile);
        $pdf->setFileSize($pdfFile->getSize());
        $pdf->setMimeType($pdfFile->getMimeType());
        $thumbPath = $this->thumbnailer->thumbnail($pdf);
        $pdf->setThumbPath($thumbPath);
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
     * Event subscriber action, called before saving an pdf to the database.
     *
     * @throws Exception
     */
    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Pdf) {
            $this->uploadFile($entity);
        }
    }

    /**
     * Event subscriber action, called before updating an pdf in the database.
     *
     * @throws Exception
     */
    public function preUpdate(PreUpdateEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Pdf) {
            $this->uploadFile($entity);
        }
    }

    /**
     * Event subscriber action. After loading an pdf entity from the database,
     * add the file object to the entity.
     */
    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Pdf) {
            $filePath = $this->uploadDir . '/' . $entity->getPath();
            if (file_exists($filePath)) {
                $entity->setFile(new File($filePath));
            } else {
                $this->logger->error('Cannot find pdf file ' . $this->uploadDir . '/' . $entity->getPath());
            }
            $thumbPath = $this->uploadDir . '/' . $entity->getThumbPath();
            if (file_exists($thumbPath)) {
                $entity->setThumbFile(new File($thumbPath));
            } else {
                $this->logger->error('Cannot find thumbnail file ' . $this->uploadDir . '/' . $entity->getPath());
            }
        }
        if ($entity instanceof PdfContainerInterface) {
            $repo = $this->em->getRepository(Pdf::class);
            /** @var Pdf[] $pdfs */
            $pdfs = $repo->findBy([
                'entity' => get_class($entity) . ':' . $entity->getId(),
            ]);
            $entity->setPdfs($pdfs);
        }
    }

    /**
     * Event subscriber action. After removing an pdf entity from the database
     * remove the pdf and thumbnail files.'.
     */
    public function postRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($entity instanceof Pdf) {
            $fs = new Filesystem();

            try {
                $fs->remove($entity->getFile());
                $fs->remove($entity->getThumbFile());
            } catch (IOExceptionInterface $ex) {
                $this->logger->error("An error occurred removing {$ex->getPath()}: {$ex->getMessage()}");
            }
        }
        if ($entity instanceof PdfContainerInterface) {
            foreach ($entity->getPdfs() as $pdf) {
                $this->em->remove($pdf);
            }
            $this->em->flush();
        }
    }

    public function acceptsPdfs(AbstractEntity $entity) : bool {
        return $entity instanceof PdfContainerInterface;
    }

    /**
     * @required
     */
    public function setThumbnailer(Thumbnailer $thumbnailer) : void {
        $this->thumbnailer = $thumbnailer;
    }

}