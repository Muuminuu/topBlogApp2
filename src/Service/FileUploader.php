<?php

namespace App\Service;

use App\Entity\UploadFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private ParameterBagInterface $params,
        private SluggerInterface $slugger,
        
    ) {
    }

    public function upload(
        UploadedFile $file,
        string $targetDirectory,
        bool $private,
        UploadFile|null $fileToRemove
    ): UploadFile
    {

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $targetDirectory = $this->params->get($targetDirectory);

        $uploadFile = new UploadFile;
        $uploadFile->setPrivate($private);
        $uploadFile->setFilename($fileName);
        $uploadFile->setName($originalFilename);
        $uploadFile->setExt($file->guessExtension());
        
        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            return $e;
        }

        return $uploadFile;
    }

}