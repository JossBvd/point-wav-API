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
    /*
        IS_AUTHENTICATED
    */
    #[Route('/api/checkout', name: 'checkout_order', methods: ['POST'])]
    public function checkout(Request $rq, ProductRepository $productRepo, EntityManagerInterface $em, PromotionRepository $promoRepo, StripeService $stripeService): JsonResponse
    {
        try {
            $user = $this->getUser();
            $data = json_decode($rq->getContent(), true);

            if (!isset($data['cart']) || !is_array($data['cart'])) {
                return new JsonResponse(['error' => 'Panier invalide'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $order = new Order();
            $total = 0;
            foreach ($data['cart'] as $item) {
                if (!isset($item['productId'], $item['quantity'])) {
                    return new JsonResponse(['error' => 'Article invalide dans le panier'], 400);
                }

                $product = $productRepo->find($item['productId']);
                if (!$product) {
                    return new JsonResponse(['error' => 'Produit non trouvé (ID ' . $item['productId'] . ')'], 404);
                }

                if ($item['quantity'] > $product->getStock()) {
                    return new JsonResponse(['error' => 'Stock insuffisant pour un produit'], 404);
                }

                $product->setStock($product->getStock() - $item['quantity']);

                $orderProduct = new OrderProduct();
                $orderProduct->setQuantity($item['quantity']);
                $orderProduct->setUnitPrice($product->getPrice());
                $orderProduct->setOrderDate(new DateTimeImmutable());
                $orderProduct->setOrderReference($order);
                $orderProduct->setProduct($product);

                $em->persist($orderProduct);


                $total += $product->getPrice() * $item['quantity'];
            }

            if (isset($data['promotion'])) {
                $promotionCode = $data['promotion'];
                $promotion = $promoRepo->findOneBy(['code' => $promotionCode]);
                if (!$promotion || !$promotion->isCurrentlyActive()) {
                    return new JsonResponse(['error' => 'Code promotionnel invalide ou expiré'], JsonResponse::HTTP_BAD_REQUEST);
                }
                $alreadyUsed = $em->getRepository(PromotionUser::class)->findOneBy([
                    'promotion' => $promotion,
                    'user' => $user
                ]);

                if ($alreadyUsed) {
                    return new JsonResponse([
                        'error' => 'Cette promotion a déjà été utilisée'
                    ], JsonResponse::HTTP_FORBIDDEN);
                }
                $reduction = 0;
                if ($promotion->getReductionType() === Reduction::PERCENTAGE) {
                    $reduction = ($total * $promotion->getReductionValue()) / 100;
                } elseif ($promotion->getReductionType() === Reduction::AMOUNT) {
                    $reduction = $promotion->getReductionValue();
                }

                $total = max(0, $total - $reduction);
                $order->setPromotion($promotion);

                $promotionUser = new PromotionUser();
                $promotionUser->setPromotion($promotion);
                $promotionUser->setUser($user);
                $promotionUser->setUsedDate(new DateTimeImmutable());

                $em->persist($promotionUser);
            }

            $order->setUser($user);
            $order->setTotalPrice($total);
            $order->setStatus(OrderStatus::IN_PROGRESS);
            $order->setCreateAt(new DateTimeImmutable());

            $em->persist($order);
            $em->flush();


            $successUrl = $this->generateUrl('app_payment_success', [
                'orderId' => $order->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $cancelUrl = $this->generateUrl('app_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);


            $session = $stripeService->createCheckoutSession(
                $total,
                'Commande #' . $order->getId(),
                $successUrl,
                $cancelUrl
            );

            $order->setStripeSessionId($session->id);
            $em->flush();

            return new JsonResponse(['checkout_url' => $session->url], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la validation de la commande'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/order', name: 'get_my_orders', methods: ['GET'])]
    public function getMyOrders(OrderRepository $orderRepo): JsonResponse
    {
        try {
            $user = $this->getUser();
            $orders = $orderRepo->findBy(['user' => $user]);

            $data = array_map(function (Order $order) {
                return  [
                    'id' => $order->getId(),
                    'user_id' => $order->getUser()->getId(),
                    'promotion_id' => $order->getPromotion(),
                    'total_price' => $order->getTotalPrice(),
                    'status' => $order->getStatus(),
                    'registration_date' => $order->getCreateAt(),
                ];
            }, $orders);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
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
    public function getOrders(OrderRepository $orderRepo): JsonResponse
    {
        try {
            $orders = $orderRepo->findAll();
            $data = array_map(function (Order $order) {
                return  [
                    'id' => $order->getId(),
                    'user_id' => $order->getUser()->getId(),
                    'promotion_id' => $order->getPromotion(),
                    'total_price' => $order->getTotalPrice(),
                    'status' => $order->getStatus(),
                    'registration_date' => $order->getCreateAt(),
                ];
            }, $orders);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des commandes'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/order/{id}', name: 'get_one_order', methods: ['GET'])]
    public function getOneOrder(OrderRepository $orderRepo, int $id): JsonResponse
    {
        try {
            $order = $orderRepo->find($id);
            return new JsonResponse([
                'id' => $order->getId(),
                'user_id' => $order->getUser()->getId(),
                'promotion_id' => $order->getPromotion(),
                'total_price' => $order->getTotalPrice(),
                'status' => $order->getStatus(),
                'registration_date' => $order->getRegistrationDate()
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la commande'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/order/{id}', name: 'update_order', methods: ['PATCH'])]
    public function updateOrder(OrderRepository $orderRepo, int $id, Request $rq, EntityManagerInterface $em): JsonResponse
    {
        try {
            $order = $orderRepo->find($id);
            $data = json_decode($rq->getContent(), true);

            if (!$order) {
                return new JsonResponse(['error' => 'Commande non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            if (isset($data['status'])) {
                $order->setStatus($data['status']);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Status modifié avec succès',
                "Order" => [
                    'id' => $order->getId(),
                    'user_id' => $order->getUser()->getId(),
                    'promotion_id' => $order->getPromotion(),
                    'total_price' => $order->getTotalPrice(),
                    'status' => $order->getStatus(),
                    'registration_date' => $order->getRegistrationDate()
                ]
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la modification du statut la commande'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
