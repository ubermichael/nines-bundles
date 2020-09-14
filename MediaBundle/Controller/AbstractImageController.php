<?php


namespace Nines\MediaBundle\Controller;


use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Form\ImageType;
use Nines\MediaBundle\Services\AbstractFileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractImageController extends AbstractController {

    public function newImageAction(Request $request, ImageContainerInterface $container, $route) {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image->setEntity($container);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            $this->addFlash('success', 'The new image has been saved.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView(),
            'entity' => $container,
        ];
    }

    public function editImageAction(Request $request, ImageContainerInterface $container, Image $image, $route) {
        if( ! $container->hasImage($image)) {
            throw new NotFoundHttpException("That image is not associated.");
        }

        $size = AbstractFileManager::getMaxUploadSize(false);
        $form = $this->createForm(ImageType::class, $image);
        $form->remove('imageFile');
        $form->add('newImageFile', FileType::class, [
            'label' => 'Replacement Image',
            'required' => false,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$size} in size.",
                'data-maxsize' => AbstractFileManager::getMaxUploadSize(true),
            ],
            'mapped' => false,
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if (($upload = $form->get('newImageFile')->getData())) {
                $image->setImageFile($upload);
                $image->preUpdate(); // force doctrine to update.
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'The image has been updated.');
            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView(),
            'entity' => $container,
        ];
    }

    public function deleteImageAction(Request $request, ImageContainerInterface $container, Image $image, $route) {
        if ( ! $this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            $this->addFlash('warning', 'Invalid security token.');
            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }
        if( ! $container->hasImage($image)) {
            throw new NotFoundHttpException("That image is not associated.");
        }
        $entityManager = $this->getDoctrine()->getManager();
        $container->removeImage($image);
        $entityManager->remove($image);
        $entityManager->flush();
        $this->addFlash('success', 'The image has been removed.');
        return $this->redirectToRoute($route, ['id' => $container->getId()]);
    }

}
