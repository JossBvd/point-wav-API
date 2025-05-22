<?php

// src/Service/OrderProductService.php

namespace App\Service;

use App\Repository\OrderProductRepository;
use Symfony\Component\Serializer\SerializerInterface;

class OrderProductService
{
    public function __construct(
        private readonly OrderProductRepository $orderProductRepo,
        private readonly SerializerInterface $serializer
    ) {}

    public function getAllOrderProducts(): string
    {
        $orderProducts = $this->orderProductRepo->findAll();

        return $this->serializer->serialize($orderProducts, 'json', [
            'groups' => ['order_product:read']
        ]);
    }

    public function getOrderProductById(int $id): ?string
    {
        $orderProduct = $this->orderProductRepo->find($id);

        if (!$orderProduct) {
            return null;
        }

        return $this->serializer->serialize($orderProduct, 'json', [
            'groups' => ['order_product:read']
        ]);
    }
}
