<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Form\AudioType;
use Nines\MediaBundle\Service\AbstractFileManager;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AudioControllerTrait {
    protected function newAudioAction(Request $request, AudioContainerInterface $container, $route) {
        $audio = new Audio();
        $form = $this->createForm(AudioType::class, $audio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $audio->setEntity($container);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($audio);
            $entityManager->flush();
            $this->addFlash('success', 'The new audio has been saved.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'audio' => $audio,
            'form' => $form->createView(),
            'entity' => $container,
            'route' => $route,
        ];
    }

    protected function editAudioAction(Request $request, AudioContainerInterface $container, Audio $audio, $route) {
        if ( ! $container->containsAudio($audio)) {
            throw new NotFoundHttpException('That audio is not associated.');
        }

        $size = AbstractFileManager::getMaxUploadSize(false);
        $form = $this->createForm(AudioType::class, $audio);
        $form->remove('audioFile');
        $form->add('newAudioFile', FileType::class, [
            'label' => 'Replacement Audio',
            'required' => false,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$size} in size.",
                'data-maxsize' => AbstractFileManager::getMaxUploadSize(true),
            ],
            'mapped' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (($upload = $form->get('newAudioFile')->getData())) {
                $audio->setAudioFile($upload);
                $audio->preUpdate(); // force doctrine to update.
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'The audio has been updated.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }

        return [
            'audio' => $audio,
            'form' => $form->createView(),
            'entity' => $container,
            'route' => $route,
        ];
    }

    protected function deleteAudioAction(Request $request, AudioContainerInterface $container, Audio $audio, $route) {
        if ( ! $this->isCsrfTokenValid('delete_audio_' . $audio->getId(), $request->request->get('_token'))) {
            $this->addFlash('warning', 'Invalid security token.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }
        if ( ! $container->containsAudio($audio)) {
            throw new NotFoundHttpException('That audio is not associated.');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $container->removeAudio($audio);
        $entityManager->remove($audio);
        $entityManager->flush();
        $this->addFlash('success', 'The audio has been removed.');

        return $this->redirectToRoute($route);
    }
}
