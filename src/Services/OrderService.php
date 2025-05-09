<?php
// src/Service/OrderService.php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\PromotionUser;
use App\Enum\OrderStatus;
use App\Enum\Reduction;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\PromotionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $productRepo,
        private readonly PromotionRepository $promoRepo,
        private readonly StripeService $stripeService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly OrderRepository $orderRepo,
        private readonly SerializerInterface $serializer,
        private readonly Security $security
    ) {}

    public function checkout(array $data, UserInterface $user): string
    {
        if (!isset($data['cart']) || !is_array($data['cart'])) {
            return new JsonResponse(['error' => 'Panier invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $total = 0;

        foreach ($data['cart'] as $item) {
            if (!isset($item['productId'], $item['quantity'])) {
                return new JsonResponse(['error' => 'Article invalide dans le panier'], 400);
            }

            $product = $this->productRepo->find($item['productId']);
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

            $this->em->persist($orderProduct);

            $total += $product->getPrice() * $item['quantity'];
        }

        if (isset($data['promotion'])) {
            $promotion = $this->promoRepo->findOneBy(['code' => $data['promotion']]);
            if (!$promotion || !$promotion->isCurrentlyActive()) {
                return new JsonResponse(['error' => 'Code promotionnel invalide ou expiré'], 400);
            }

            $alreadyUsed = $this->em->getRepository(PromotionUser::class)->findOneBy([
                'promotion' => $promotion,
                'user' => $user
            ]);
            if ($alreadyUsed) {
                return new JsonResponse(['error' => 'Cette promotion a déjà été utilisée'], 403);
            }

            $reduction = match ($promotion->getReductionType()) {
                Reduction::PERCENTAGE => ($total * $promotion->getReductionValue()) / 100,
                Reduction::AMOUNT => $promotion->getReductionValue(),
                default => 0,
            };

            $total = max(0, $total - $reduction);
            $order->setPromotion($promotion);

            $promoUser = new PromotionUser();
            $promoUser->setPromotion($promotion);
            $promoUser->setUser($user);
            $promoUser->setUsedDate(new DateTimeImmutable());

            $this->em->persist($promoUser);
        }

        $order->setUser($user);
        $order->setTotalPrice($total);
        $order->setStatus(OrderStatus::IN_PROGRESS);
        $order->setCreateAt(new DateTimeImmutable());

        $this->em->persist($order);
        $this->em->flush();

        $successUrl = $this->urlGenerator->generate('app_payment_success', [
            'orderId' => $order->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $cancelUrl = $this->urlGenerator->generate('app_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = $this->stripeService->createCheckoutSession(
            $total,
            'Commande #' . $order->getId(),
            $successUrl,
            $cancelUrl
        );

        $order->setStripeSessionId($session->id);
        $this->em->flush();
        return $session->url;
    }

    public function getAllOrders(): string
    {
        $orders = $this->orderRepo->findAll();
        return $this->serializer->serialize($orders, 'json', ['groups' => 'order:read']);
    }

    public function getOrderById(int $id): string
    {
        $order = $this->orderRepo->find($id);
        return $this->serializer->serialize($order, 'json', ['groups' => 'order:read']);
    }

    public function getMyOrders(): string
    {
        $user = $this->security->getUser();
        $orders = $this->orderRepo->findBy(['user' => $user]);

        return $this->serializer->serialize($orders, 'json', ['groups' => 'order:read']);
    }

    public function updateOrder(int $id, Request $rq): string
    {
        $data = json_decode($rq->getContent(), true);
        $order = $this->orderRepo->find($id);
        
        if (isset($data['status'])) {
            $order->setStatus(OrderStatus::from($data['status']));
        }

        $this->em->flush();

        return $this->serializer->serialize($order, 'json', ['groups' => 'order:read']);
    }
}
