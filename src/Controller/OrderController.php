<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\PromotionUser;
use App\Enum\OrderStatus;
use App\Enum\Reduction;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use App\Service\OrderService;
use App\Service\StripeService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderRepository $orderRepo
    ) {}
    /*
        IS_AUTHENTICATED
    */
    #[Route('/api/checkout', name: 'checkout_order', methods: ['POST'])]
    public function checkout(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            $data = json_decode($request->getContent(), true);

            $checkoutUrl = $this->orderService->checkout($data, $user);

            return new JsonResponse(['checkout_url' => $checkoutUrl], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la validation de la commande'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/order', name: 'get_my_orders', methods: ['GET'])]
    public function getMyOrders(): JsonResponse
    {
        try {
            $ordersJson = $this->orderService->getMyOrders();
            return new JsonResponse($ordersJson, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des commandes de l\'utilisateur'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /* 
        ROLE_ADMIN
    */
    #[Route('/api/admin/order', name: 'get_orders', methods: ['GET'])]
    public function getAllOrders(): JsonResponse
    {
        try {
            $orders = $this->orderService->getAllOrders();
            return new JsonResponse($orders, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des commandes'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/order/{id}', name: 'get_one_order', methods: ['GET'])]
    public function getOneOrder(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderById($id);
            return new JsonResponse($order, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la commande'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/order/{id}', name: 'update_order', methods: ['PATCH'])]
    public function updateOrder(int $id, Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->updateOrder($id, $request);
            return new JsonResponse($order, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la modification du statut de la commande'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
