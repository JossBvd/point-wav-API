<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepo,
        private readonly CategoryRepository $categoryRepo,
        private readonly ImageService $imageService,
        private readonly SerializerInterface $serializer,
        private readonly string $uploadDir
    ) {
    }

    public function getAllProducts(): string
    {
        $products = $this->productRepo->findAll();
        return $this->serializer->serialize($products, 'json', ['groups' => 'product:read']);
    }

    public function getProductById(int $id): ?string
    {
        $product = $this->productRepo->find($id);
        if (!$product) {
            return null;
        }

        return $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
    }

    public function createProduct(array $data, ?UploadedFile $imageFile): string
    {
        $product = new Product();
        $product->setName($data['name']);
        $product->setBrand($data['brand']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setStock($data['stock']);
        $product->setRegistrationDate(new DateTimeImmutable());
        $product->setIsActive(true);

        $category = $this->categoryRepo->find($data['category_id']);
        $product->setCategory($category);

        if ($imageFile) {
            $this->addImageToProduct($product, $imageFile);
        }

        $this->em->persist($product);
        $this->em->flush();

        return $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
    }

    public function updateProduct(int $id, array $data): ?string
    {
        $product = $this->productRepo->find($id);
        if (!$product) {
            return null;
        }

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }

        if (isset($data['brand'])) {
            $product->setBrand($data['brand']);
        }

        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }

        if (isset($data['price'])) {
            $product->setPrice($data['price']);
        }

        if (isset($data['stock'])) {
            $product->setStock($data['stock']);
        }

        if (isset($data['isActive'])) {
            $product->setIsActive($data['isActive']);
        }

        if (isset($data['categoryId'])) {
            $category = $this->categoryRepo->find($data['categoryId']);
            if ($category) {
                $product->setCategory($category);
            }
        }

        $this->em->flush();

        return $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
    }

    public function deleteProduct(int $id): bool|string
    {
        $product = $this->productRepo->find($id);
        if (!$product) {
            return false;
        }

        $product->setIsActive(false);
        $this->em->flush();

        return true;
    }

    public function addImageToProduct(Product $product, UploadedFile $imageFile): string
    {
        $filename = $this->imageService->uploadImage($imageFile);

        $image = new Image();
        $image->setUrl('/uploads/images/' . $filename);
        $image->setRanking(1);
        $image->setProduct($product);

        $this->em->persist($image);
        $this->em->flush();

        return $this->serializer->serialize($product, 'json', ['groups' => 'product:read']);
    }
}