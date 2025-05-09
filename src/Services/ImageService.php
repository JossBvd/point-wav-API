<?php

namespace App\Service;

use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class ImageService
{
    private array $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    public function __construct(private readonly string $uploadDir, private readonly ImageRepository $imageRepo, private readonly EntityManagerInterface $em, private readonly SerializerInterface $serializer) {}

    public function uploadImage(UploadedFile $file): string
    {
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException('Type de fichier non supporté');
        }

        $filename = uniqid('img_') . '.' . $file->guessExtension();
        $file->move($this->uploadDir, $filename);

        return $filename;
    }

    public function deleteImage(string $relativeUrl): void
    {
        $filePath = $this->uploadDir . '/' . basename($relativeUrl);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getAllImages(): string
    {
        $images = $this->imageRepo->findAll();
        return $this->serializer->serialize($images, 'json', ['groups' => 'image:read']);
    }

    public function getImageById(int $id): ?string
    {
        $image = $this->imageRepo->find($id);
        return $this->serializer->serialize($image, 'json', ['groups' => 'image:read']);
    }

    public function updateImage(int $id, array $data): ?string
    {
        $image = $this->imageRepo->find($id);
        if (isset($data['ranking'])) {
            $image->setRanking($data['ranking']);
        }
        $this->em->flush();
        return $this->serializer->serialize($image, 'json', ['groups' => 'image:read']);
    }

    public function deleteImageFromTable(int $id): bool|string
    {
        $image = $this->imageRepo->find($id);

        if (!$image) {
            return 'not_found';
        }

        if ($image->getCategory() !== null) {
            return 'has_category';
        }
        $this->em->remove($image);
        $this->em->flush();
        $this->deleteImage($image->getUrl());
        return true;
    }
}
