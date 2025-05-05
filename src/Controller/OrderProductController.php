<?php

namespace App\Controller;

use App\Entity\OrderProduct;
use App\Repository\OrderProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class OrderProductController extends AbstractController
{
    /* 
        ROLE_ADMIN
    */
    #[Route('/api/admin/order-product', name: 'get_order_product', methods: ['GET'])]
    public function getOrderProduct(OrderProductRepository $orderProductRepo): JsonResponse
    {
        try {
            $orderProducts = $orderProductRepo->findAll();
            $data = array_map(function (OrderProduct $orderProduct) {
                return  [
                    'id' => $orderProduct->getId(),
                    'order_id' => $orderProduct->getOrderReference()->getId(),
                    'product_id' => $orderProduct->getProduct()->getId(),
                    'quantity' => $orderProduct->getQuantity(),
                    'unit_price' => $orderProduct->getUnitPrice(),
                    'order_date' => $orderProduct->getOrderDate(),
                ];
            }, $orderProducts);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des produits commandés'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/order-product/{id}', name: 'get_one_order_product', methods: ['GET'])]
    public function getOneOrderProduct(OrderProductRepository $orderProductRepo, int $id): JsonResponse
    {
        try {
            $orderProduct = $orderProductRepo->find($id);
            return new JsonResponse([
                'id' => $orderProduct->getId(),
                'order_id' => $orderProduct->getOrderReference()->getId(),
                'product_id' => $orderProduct->getProduct()->getId(),
                'quantity' => $orderProduct->getQuantity(),
                'unit_price' => $orderProduct->getUnitPrice(),
                'order_date' => $orderProduct->getOrderDate(),
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération du produit commandé'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
