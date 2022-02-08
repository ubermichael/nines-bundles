<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Form\ImageType;
use Nines\MediaBundle\Service\AbstractFileManager;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ImageControllerTrait {
    abstract public function createForm(string $type, $data = null, array $options = []);

    abstract public function redirectToRoute(string $route, array $parameters = [], int $status = 302) : RedirectResponse;

    abstract public function addFlash(string $type, $message);

    abstract public function isCsrfTokenValid(string $id, ?string $token);

    /**
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     */
    protected function newImageAction(Request $request, EntityManagerInterface $em, ImageContainerInterface $container, string $route) {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image->setEntity($container);
            $em->persist($image);
            $em->flush();
            $this->addFlash('success', 'The new image has been saved.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView(),
            'entity' => $container,
            'route' => $route,
        ];
    }

    /**
     * @param mixed $route
     *
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     */
    protected function editImageAction(Request $request, EntityManagerInterface $em, ImageContainerInterface $container, Image $image, $route) {
        if ( ! $container->containsImage($image)) {
            throw new NotFoundHttpException('That image is not associated.');
        }

        $size = AbstractFileManager::getMaxUploadSize(false);
        $form = $this->createForm(ImageType::class, $image);
        $form->remove('file');
        $form->add('newFile', FileType::class, [
            'label' => 'Replacement Image',
            'required' => false,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$size} in size.",
                'data-maxsize' => AbstractFileManager::getMaxUploadSize(true),
            ],
            'mapped' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (($upload = $form->get('newFile')->getData())) {
                $image->setFile($upload);
                $image->preUpdate(); // force doctrine to update.
            }
            $em->flush();
            $this->addFlash('success', 'The image has been updated.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView(),
            'entity' => $container,
            'route' => $route,
        ];
    }

    protected function deleteImageAction(Request $request, EntityManagerInterface $em, ImageContainerInterface $container, Image $image, string $route) : RedirectResponse {
        if ( ! $this->isCsrfTokenValid('delete_image' . $image->getId(), $request->request->get('_token'))) {
            $this->addFlash('warning', 'Invalid security token.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }
        if ( ! $container->containsImage($image)) {
            throw new NotFoundHttpException('That image is not associated.');
        }
        $container->removeImage($image);
        $em->remove($image);
        $em->flush();
        $this->addFlash('success', 'The image has been removed.');

        return $this->redirectToRoute($route, ['id' => $container->getId()]);
    }
}
