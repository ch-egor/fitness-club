<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\Client;
use App\Service\PhotoUploader;

class PhotoUploadListener
{
    private $photoUploader;

    public function __construct(PhotoUploader $photoUploader)
    {
        $this->photoUploader = $photoUploader;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Client) {
            return;
        }

        if ($fileName = $entity->getPhoto()) {
            $entity->setPhoto(new File($this->photoUploader->getTargetDirectory() . '/' . $fileName));
        }
    }

    private function uploadFile($entity)
    {
        // upload only works for Client entities
        if (!$entity instanceof Client) {
            return;
        }

        $file = $entity->getPhoto();

        // only upload new files
        if ($file instanceof UploadedFile) {
            $fileName = $this->photoUploader->upload($file);
            $entity->setPhoto($fileName);
        } elseif ($file instanceof File) {
            // prevents the full file path being saved on updates
            // as the path is set on the postLoad listener
            $entity->setPhoto($file->getFilename());
        }
    }
}
