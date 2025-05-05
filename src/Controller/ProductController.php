<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    /*
        PUBLIC
    */
    #[Route('/api/product', name: 'get_products', methods: ['GET'])]
    public function getProduct(ProductRepository $productRepo): JsonResponse
    {
        try {
            $products = $productRepo->findAll();
            $data = array_map(function (Product $product) {
                return  [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'brand' => $product->getBrand(),
                    'category_id' => $product->getCategory()->getId(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'stock' => $product->getStock(),
                    'registrationDate' => $product->getRegistrationDate(),
                    'isActive' => $product->isActive()
                ];
            }, $products);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la liste des produits'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/product/{id}', name: 'get_one_product', methods: ['GET'])]
    public function getOneProduct(ProductRepository $productRepo, int $id): JsonResponse
    {
        try {
            $product = $productRepo->find($id);
            return new JsonResponse([
                'id' => $product->getId(),
                'name' => $product->getName(),
                'brand' => $product->getBrand(),
                'category_id' => $product->getCategory()->getId(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'registrationDate' => $product->getRegistrationDate(),
                'isActive' => $product->isActive()
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération d\'un produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*
        ROLE_ADMIN
    */
    #[Route('/api/admin/product', name: 'add_product', methods: ['POST'])]
    public function addProduct(Request $rq, EntityManagerInterface $em, CategoryRepository $categoryRepo): JsonResponse
    {
        try {

            $product = new Product();
            $name = $rq->request->get('name');
            $brand = $rq->request->get('brand');
            $description = $rq->request->get('description');
            $price = $rq->request->get('price');
            $stock = $rq->request->get('stock');

            $product->setName($name);
            $product->setBrand($brand);
            $product->setDescription($description);
            $product->setPrice($price);
            $product->setStock($stock);
            $product->setRegistrationDate(new DateTimeImmutable());
            $product->setIsActive(true);

            $category = $categoryRepo->find($rq->request->get('category_id'));
            $product->setCategory($category);

            // Handle Image
            $uploadedImage = $rq->files->get('image');

            $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            if (!in_array($uploadedImage->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse(['error' => 'Type de fichier non supporté'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $filename = uniqid('img_') . '.' . $uploadedImage->guessExtension();

            $uploadedImage->move($this->getParameter('upload_image_directory'), $filename);

            $image = new Image();
            $image->setUrl('/uploads/images/' . $filename);
            $image->setRanking(1);
            $image->setProduct($product);



            $em->persist($image);
            $em->persist($product);
            $em->flush();

            return new JsonResponse([
                'message' => 'Produit ajouté avec succès',
                'Product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'brand' => $product->getBrand(),
                    'category_id' => $product->getCategory()->getId(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'stock' => $product->getStock(),
                    'registrationDate' => $product->getRegistrationDate(),
                    'isActive' => $product->isActive()
                ]
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la création d\'un produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/product/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(EntityManagerInterface $em, ProductRepository $productRepo, int $id): JsonResponse
    {
        try {
            $product = $productRepo->find($id);
            $product->setIsActive(false);
            $em->flush();
            return new JsonResponse(['message' => 'Produit supprimé avec succès'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la suppression du produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/product/{id}', name: 'update_product', methods: ['PUT'])]
    public function updateProduct(Request $rq, EntityManagerInterface $em, ProductRepository $productRepo, CategoryRepository $categoryRepo, int $id): JsonResponse
    {
        try {
            $product = $productRepo->find($id);
            $data = json_decode($rq->getContent(), true);

            if (!$product) {
                return new JsonResponse(['error' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
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
                $category = $categoryRepo->find($data['categoryId']);
                if ($category) {
                    $product->setCategory($category);
                } else {
                    return new JsonResponse(['error' => 'Catégorie inconnue'], JsonResponse::HTTP_BAD_REQUEST);
                }
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Produit modifié avec succès',
                'Product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'brand' => $product->getBrand(),
                    'category_id' => $product->getCategory()->getId(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'stock' => $product->getStock(),
                    'registrationDate' => $product->getRegistrationDate()
                ]
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour du produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/product/{id}/add-image', name: 'add_image_product', methods: ['POST'])]
    public function addImageProduct(Request $rq, EntityManagerInterface $em, ProductRepository $productRepo, int $id): JsonResponse
    {
        try {
            $product = $productRepo->find($id);

            $uploadedImage = $rq->files->get('image');

            $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            if (!in_array($uploadedImage->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse(['error' => 'Type de fichier non supporté'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $filename = uniqid('img_') . '.' . $uploadedImage->guessExtension();

            $uploadedImage->move($this->getParameter('upload_image_directory'), $filename);

            $image = new Image();
            $image->setUrl('/uploads/images/' . $filename);
            $image->setRanking(1);
            $image->setProduct($product);

            $em->persist($image);
            $em->flush();

            return new JsonResponse(['message' => 'Image ajoutée au produit avec succès'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de l\ajout d\image au produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
