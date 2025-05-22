<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Image;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepo,
        private ImageService $imageService,
        private string $uploadDir,
        /** @var SerializerInterface */
        private SerializerInterface $serializer
    ) {}

    public function getAllCategories(): string
    {
        $categories = $this->categoryRepo->findAll();

        return $this->serializer->serialize($categories, 'json', ['groups' => 'category:read']);
    }

    public function createCategory(string $name, UploadedFile $uploadedImage): string
    {
        $filename = $this->imageService->uploadImage($uploadedImage);

        $image = new Image();
        $image->setUrl('/uploads/images/' . $filename);
        $image->setRanking(1);

        $category = new Category();
        $category->setName($name);
        $category->setImage($image);

        $this->em->persist($image);
        $this->em->persist($category);
        $this->em->flush();

        return $this->serializer->serialize($category, 'json', ['groups' => 'category:read']);
    }

    public function updateCategory(int $id, ?string $name, ?UploadedFile $uploadedImage): ?string
    {
        $category = $this->categoryRepo->find($id);

        if (!$category) return null;

        if ($name) {
            $category->setName($name);
        }

        if ($uploadedImage) {
            $filename = $this->imageService->uploadImage($uploadedImage);

            $newImage = new Image();
            $newImage->setUrl('/uploads/images/' . $filename);
            $newImage->setRanking(1);
            $this->em->persist($newImage);

            $category->setImage($newImage);

            $oldImage = $category->getImage();
            if ($oldImage) {
                $this->imageService->deleteImage($this->uploadDir . '/' . basename($oldImage->getUrl()));
                $this->em->remove($oldImage);
            }
        }

        $this->em->flush();
        return $this->serializer->serialize($category, 'json', ['groups' => 'category:read']);
    }

    public function deleteCategory(int $id): bool|string
    {
        $category = $this->categoryRepo->find($id);

        if (!$category) return 'not_found';

        if (!$category->getProducts()->isEmpty()) return 'has_products';

        $image = $category->getImage();
        if ($image) {
            $this->imageService->deleteImage($this->uploadDir . '/' . basename($image->getUrl()));
            $this->em->remove($image);
        }

        $this->em->remove($category);
        $this->em->flush();
        return true;
    }
}
