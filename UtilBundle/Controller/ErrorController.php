<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

class ErrorController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    public function show(Request $request, FlattenException $exception, Environment $twig, SerializerInterface $serializer, ?DebugLoggerInterface $dbgLogger = null) : Response {
        $format = $request->getRequestFormat();
        $status = $exception->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;

        $data = [
            'date' => date('c'),
            'status' => $status,
            'url' => $request->getRealMethod() . ' ' . $request->getUri(),
            'referrer' => $request->headers->get('referer'),
            'ip' => implode(';', $request->getClientIps()),
            'class' => $exception->getClass(),
            'message' => $exception->getMessage(),
            'backtrace' => $exception->getTrace(),
            'trim' => mb_strlen($this->getParameter('kernel.project_dir')) + 1,
        ];

        $content = '';
        $mime = 'text/html';

        switch ($format) {
            case 'json':
                $content = $serializer->serialize($data, 'json');
                $mime = 'application/json';

                break;

            case 'xml':
                $content = $serializer->serialize($data, 'xml');
                $mime = 'application/xml';

                break;

            default:
                $template = '@NinesUtil/error/error.html.twig';
                if ($twig->getLoader()->exists("@NinesUtil/error/error{$status}.html.twig")) {
                    $template = "@NinesUtil/error/error{$status}.html.twig";
                }
                $content = $twig->render($template, $data);
        }

        return new Response($content, $status, ['Content-Type' => $mime]);
    }
}
