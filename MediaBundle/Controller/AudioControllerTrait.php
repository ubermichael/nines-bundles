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
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Form\AudioType;
use Nines\MediaBundle\Service\AbstractFileManager;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AudioControllerTrait {
    abstract public function createForm(string $type, $data = null, array $options = []);

    abstract public function redirectToRoute(string $route, array $parameters = [], int $status = 302) : RedirectResponse;

    abstract public function addFlash(string $type, $message);

    abstract public function isCsrfTokenValid(string $id, ?string $token);

    /**
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     */
    protected function newAudioAction(Request $request, EntityManagerInterface $em, AudioContainerInterface $container, string $route) {
        $audio = new Audio();
        $form = $this->createForm(AudioType::class, $audio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $audio->setEntity($container);
            $em->persist($audio);
            $em->flush();
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

    /**
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     */
    protected function editAudioAction(Request $request, EntityManagerInterface $em, AudioContainerInterface $container, Audio $audio, string $route) {
        if ( ! $container->containsAudio($audio)) {
            throw new NotFoundHttpException('That audio is not associated.');
        }

        $size = AbstractFileManager::getMaxUploadSize(false);
        $form = $this->createForm(AudioType::class, $audio);
        $form->remove('file');
        $form->add('newFile', FileType::class, [
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
            if (($upload = $form->get('newFile')->getData())) {
                $audio->setFile($upload);
                $audio->preUpdate(); // force doctrine to update.
            }
            $em->flush();
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

    /**
     * @throws Exception
     */
    protected function deleteAudioAction(Request $request, EntityManagerInterface $em, AudioContainerInterface $container, Audio $audio, string $route) : RedirectResponse {
        if ( ! $this->isCsrfTokenValid('delete_audio' . $audio->getId(), $request->request->get('_token'))) {
            $this->addFlash('warning', 'Invalid security token.');

            return $this->redirectToRoute($route, ['id' => $container->getId()]);
        }
        if ( ! $container->containsAudio($audio)) {
            throw new NotFoundHttpException('That audio is not associated.');
        }
        $container->removeAudio($audio);
        $em->remove($audio);
        $em->flush();
        $this->addFlash('success', 'The audio has been removed.');

        return $this->redirectToRoute($route, ['id' => $container->getId()]);
    }
}
