<?php

namespace App\Controller;

use App\Service\OrderProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class OrderProductController extends AbstractController
{
    public function __construct(
        private readonly OrderProductService $orderProductService
    ) {}

    #[Route('/api/admin/order-product', name: 'get_order_product', methods: ['GET'])]
    public function getOrderProduct(): JsonResponse
    {
        try {
            $data = $this->orderProductService->getAllOrderProducts();
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des produits commandés'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/order-product/{id}', name: 'get_one_order_product', methods: ['GET'])]
    public function getOneOrderProduct(int $id): JsonResponse
    {
        try {
            $data = $this->orderProductService->getOrderProductById($id);

            if (!$data) {
                return new JsonResponse(['error' => 'Produit commandé non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération du produit commandé'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
