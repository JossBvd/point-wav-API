<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(private readonly ProductService $productService, private readonly ProductRepository $productRepo)
    {
        
    }
    /*
        PUBLIC
    */
    #[Route('/api/product', name: 'get_products', methods: ['GET'])]
    public function getAllProducts(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();
            return new JsonResponse($products, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la liste des produits'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/product/{id}', name: 'get_one_product', methods: ['GET'])]
    public function getProductById(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);
            if (!$product) {
                return new JsonResponse(['error' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }
            return new JsonResponse($product, JsonResponse::HTTP_OK);
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
    public function createProduct(Request $rq): JsonResponse
    {
        try {
            $data = [
                'name' => $rq->request->get('name'),
                'brand' => $rq->request->get('brand'),
                'description' => $rq->request->get('description'),
                'price' => $rq->request->get('price'),
                'stock' => $rq->request->get('stock'),
                'category_id' => $rq->request->get('category_id'),
            ];

            $imageFile = $rq->files->get('image');
            $product = $this->productService->createProduct($data, $imageFile);

            return new JsonResponse($product, JsonResponse::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la création d\'un produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/product/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function deleteProduct(int $id): JsonResponse
    {
        try {
            $success = $this->productService->deleteProduct($id);

            return match ($success) {
                true => new JsonResponse(['message' => 'Produit supprimé avec succès'], JsonResponse::HTTP_OK),
                false => new JsonResponse(['error' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND)
            };
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la suppression du produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/product/{id}', name: 'update_product', methods: ['PUT'])]
    public function updateProduct(Request $rq, ProductService $productService, int $id): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);
            $product = $productService->updateProduct($id, $data);
            
            if (!$product) {
                return new JsonResponse(['error' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($product, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour du produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/product/{id}/add-image', name: 'add_image_product', methods: ['POST'])]
    public function addImageToProduct(Request $rq, int $id): JsonResponse
    {
        try {
            $imageFile = $rq->files->get('image');
            if (!$imageFile) {
                return new JsonResponse(['error' => 'Aucune image fournie'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $product = $this->productRepo->find($id);
            if (!$product) {
                return new JsonResponse(['error' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            $serializedProduct = $this->productService->addImageToProduct($product, $imageFile);

            return new JsonResponse($serializedProduct, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de l\'ajout d\'image au produit'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}