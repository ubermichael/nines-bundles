<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\EditorBundle\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditorController extends AbstractController {
    public const FORBIDDEN = '/[^a-z0-9_. -]/i';

    private function getUploadDir() {
        $uploadDir = $this->getParameter('nines.editor.upload_dir');
        if ('/' !== $uploadDir[0]) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/' . $uploadDir;
        }

        return $uploadDir;
    }

    /**
     * @Route("/upload/image", name="editor_upload", methods={"POST"})
     * @IsGranted("ROLE_USER")
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function editorUploadImageAction(Request $request, Packages $assetsManager) {
        if (1 !== $request->files->count()) {
            throw new BadRequestHttpException('Expected one file parameter. Got ' . $request->files->count() . ' instead.');
        }

        $uploadDir = $this->getUploadDir();
        $uploadFile = $request->files->get('file');

        $name = preg_replace(self::FORBIDDEN, '_', $uploadFile->getClientOriginalName());
        $info = pathinfo($name);
        $slug = strtr(base64_encode(random_bytes(12)), '+/=', '-__');

        $filename = $info['filename'] . '_' . $slug . '.' . $info['extension'];
        $uploadFile->move($uploadDir, $filename);

        return new JsonResponse(
            ['location' => $this->generateUrl('editor_image', [
                'filename' => $filename,
            ], UrlGeneratorInterface::ABSOLUTE_PATH)]
        );
    }

    /**
     * @param mixed $filename
     * @Route("/upload/image/{filename}", name="editor_image", methods={"GET"})
     *
     * @return BinaryFileResponse
     */
    public function editorViewImageAction(Request $request, $filename) {
        if (preg_match(self::FORBIDDEN, $filename)) {
            throw new BadRequestHttpException('Invalid file name: ' . $filename);
        }
        $uploadDir = $this->getUploadDir();
        $path = $uploadDir . '/' . $filename;
        if ( ! file_exists($path)) {
            throw new NotFoundHttpException("Requested file {$filename} could not be found.");
        }

        return new BinaryFileResponse($path);
    }
}
