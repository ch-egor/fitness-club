<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoUploader
{
    private $targetDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->targetDirectory = $params->get('app.photos_directory');
    }

    public function upload(?UploadedFile $file)
    {
        if (!$file) {
            return null;
        }

        $fileName = md5(uniqid()) . '.' . $file->guessExtension();

        $file->move($this->getTargetDirectory(), $fileName);

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
