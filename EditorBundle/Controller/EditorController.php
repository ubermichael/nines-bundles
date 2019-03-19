<?php

namespace Nines\EditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class EditorController extends Controller
{
    /**
     * @param Request $request
     * @Route("/upload/image", name="editor_upload")
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
