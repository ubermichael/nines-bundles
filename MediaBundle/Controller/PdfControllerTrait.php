<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Entity\PdfContainerInterface;
use Nines\MediaBundle\Form\PdfType;
use Nines\MediaBundle\Service\AbstractFileManager;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait PdfControllerTrait {
    protected function newPdfAction(Request $request, PdfContainerInterface $container, $route) {
        $pdf = new Pdf();
        $form = $this->createForm(PdfType::class, $pdf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pdf->setEntity($container);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pdf);
            $entityManager->flush();
            $this->addFlash('success', 'The new pdf has been saved.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'pdf' => $pdf,
            'form' => $form->createView(),
            'entity' => $container,
            'route' => $route,
        ];
    }

    protected function editPdfAction(Request $request, PdfContainerInterface $container, Pdf $pdf, $route) {
        if ( ! $container->containsPdf($pdf)) {
            throw new NotFoundHttpException('That pdf is not associated.');
        }

        $size = AbstractFileManager::getMaxUploadSize(false);
        $form = $this->createForm(PdfType::class, $pdf);
        $form->remove('file');
        $form->add('newFile', FileType::class, [
            'label' => 'Replacement Pdf',
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
                $pdf->setFile($upload);
                $pdf->preUpdate(); // force doctrine to update.
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'The pdf has been updated.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'pdf' => $pdf,
            'form' => $form->createView(),
            'entity' => $container,
            'route' => $route,
        ];
    }

    protected function deletePdfAction(Request $request, PdfContainerInterface $container, Pdf $pdf, $route) {
        if ( ! $this->isCsrfTokenValid('delete' . $pdf->getId(), $request->request->get('_token'))) {
            $this->addFlash('warning', 'Invalid security token.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }
        if ( ! $container->containsPdf($pdf)) {
            throw new NotFoundHttpException('That pdf is not associated.');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $container->removePdf($pdf);
        $entityManager->remove($pdf);
        $entityManager->flush();
        $this->addFlash('success', 'The pdf has been removed.');

        return $this->redirectToRoute($route, ['id' => $container->getId()]);
    }
}
