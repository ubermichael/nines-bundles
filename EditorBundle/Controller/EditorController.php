<?php

namespace Nines\EditorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditorController extends Controller
{

    private function getUploadDir(){
        $uploadDir = $this->getParameter('nines.editor.upload_dir');
        if( ! $uploadDir[0] === '/') {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/' . $uploadDir;
        }
        return $uploadDir;
    }

    /**
     * @param Request $request
     * @Route("/upload/image", name="editor_upload", methods={"POST"})
     * @Security("has_role('ROLE_USER')")

     */
    public function editorUploadImageAction(Request $request, Packages $assetsManager)
    {
        if ($request->files->count() != 1) {
            throw new BadRequestHttpException("Expected one file parameter. Got " . $request->files->count() . " instead.");
        }

        $uploadDir = $this->getUploadDir();
        $uploadFile = $request->files->get('file');

        $clientName = preg_replace("/[^a-z0-9 _-]/i", '', $uploadFile->getClientOriginalName());
        $name = uniqid($clientName . '_') . '.' . $uploadFile->guessExtension();
        $uploadFile->move($uploadDir, $name);

        return new JsonResponse(array('location' => $this->generateUrl('editor_image', array('filename' => $name), UrlGeneratorInterface::ABSOLUTE_PATH)));
    }

    /**
     * @param Request $request
     * @Route("/upload/image/{filename}", name="editor_image", methods={"GET"})

     */
    public function editorViewImageAction(Request $request, $filename)
    {
        if (!preg_match('/^[a-z0-9 ._-]*$/i', $filename)) {
            throw new BadRequestHttpException('Invalid file name: ' . $filename);
        }
        $uploadDir = $this->getUploadDir();
        $path = $uploadDir . '/' . $filename;
        if (!file_exists($path)) {
            throw new FileNotFoundException("Cannot find {$filename}.");
        }
        return new BinaryFileResponse($path);
    }
}
