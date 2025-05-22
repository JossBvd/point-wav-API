<?php

namespace App\Service;

use App\Entity\Refund;
use App\Repository\OrderProductRepository;
use App\Repository\RefundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RefundService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderProductRepository $orderProductRepo,
        private readonly SerializerInterface $serializer,
        private readonly RefundRepository $refundRepo
    ) {}

    public function createRefund(array $data, int $orderProductId): array|string
    {
        $orderProduct = $this->orderProductRepo->find($orderProductId);

        if (!$orderProduct) {
            return ['error' => 'Aucun produit commandé sélectionné', 'status' => 400];
        }

        $refund = new Refund();
        $refund->setOrderProduct($orderProduct);
        $refund->setRefundedQuantity($data['quantity']);
        $refund->setStatus($data['status']);
        $refund->setRefundDate(new \DateTimeImmutable());
        if (isset($data['stripe_refund_id'])) $refund->setStripeRefundId($data['stripe_refund_id'] ?? null);

        $this->em->persist($refund);
        $this->em->flush();

        return $this->serializer->serialize($refund, 'json', ['groups' => 'refund:read']);
    }

    public function getAllRefunds(): string
    {
        $refunds = $this->refundRepo->findAll();

        return $this->serializer->serialize($refunds, 'json', ['groups' => 'refund:read']);
    }

    public function getOneRefund(int $id): array|string
    {
        $refund = $this->refundRepo->find($id);

        if (!$refund) {
            return ['error' => 'Remboursement introuvable', 'status' => 404];
        }


        return $this->serializer->serialize($refund, 'json', ['groups' => 'refund:read']);
    }

    public function updateRefund(int $id, array $data): array|string
    {
        $refund = $this->refundRepo->find($id);

        if (!$refund) {
            return ['error' => 'Remboursement introuvable', 'status' => 404];
        }

        if (isset($data['status'])) {
            $refund->setStatus($data['status']);
        }

        if (isset($data['stripe_refund_id'])) {
            $refund->setStripeRefundId($data['stripe_refund_id']);
        }

        $this->em->flush();
        return $this->serializer->serialize($refund, 'json', ['groups' => 'refund:read']);
    }
}
