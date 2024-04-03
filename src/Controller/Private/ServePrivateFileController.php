<?php

namespace App\Controller\Private;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ServePrivateFileController extends AbstractController
{
   
    /**
     * Returns a private file for display.
     *
     * @param string $path
     * @return BinaryFileResponse
     */
    #[Route('/serve-private-file/{path}', name: 'app_serve_private_file', methods: ['GET'])]
    public function fileServe(string $path): BinaryFileResponse
    {
        $absolutePath = $this->getParameter('private_avatar_directory') . '/' . $path;

        return $this->file($absolutePath);
    }
}