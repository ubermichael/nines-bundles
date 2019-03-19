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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditorController extends Controller
{
    /**
     * @param Request $request
     * @Route("/upload/image", name="editor_upload")
     * @Security("has_role('ROLE_USER')")
     * @Method("POST")
     */
    public function editorUploadImageAction(Request $request, Packages $assetsManager)
    {
        if ($request->files->count() != 1) {
            throw new BadRequestHttpException("Expected one file parameter. Got " . $request->files->count() . " instead.");
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/' . $this->getParameter('nines.editor.upload_dir');
        $uploadFile = $request->files->get('file');

        $clientName = preg_replace("/[^a-z0-9 _-]/i", '', $uploadFile->getClientOriginalName());
        $name = uniqid($clientName . '_') . '.' . $uploadFile->guessExtension();
        $uploadFile->move($uploadDir, $name);

        return new JsonResponse(array('location' => $this->generateUrl('editor_image', array('filename' => $name), UrlGeneratorInterface::ABSOLUTE_PATH)));
    }

    /**
     * @param Request $request
     * @Route("/upload/image/{filename}", name="editor_image")
     * @Method("GET")
     */
    public function editorViewImageAction(Request $request, $filename)
    {
        if (!preg_match('/^[a-z0-9 ._-]*$/i', $filename)) {
            throw new BadRequestHttpException('Invalid file name: ' . $filename);
        }
        $uploadDir = $this->getParameter('kernel.project_dir') . '/' . $this->getParameter('nines.editor.upload_dir');
        $path = $uploadDir . '/' . $filename;
        if (!file_exists($path)) {
            throw new FileNotFoundException("Cannot find {$filename}.");
        }
        return new BinaryFileResponse($path);
    }
}
