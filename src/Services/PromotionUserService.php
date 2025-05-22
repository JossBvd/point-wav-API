<?php

namespace App\Service;

use App\Repository\PromotionUserRepository;
use Symfony\Component\Serializer\SerializerInterface;

class PromotionUserService
{
    public function __construct(
        private readonly PromotionUserRepository $promotionUserRepo,
        private readonly SerializerInterface $serializer
    ) {}

    public function getAll(): string
    {
        $promotionUsers = $this->promotionUserRepo->findAll();
        return $this->serializer->serialize($promotionUsers, 'json', ['groups' => 'promotion_user:read']);
    }

    public function getOne(int $id): ?string
    {
        $promotionUser = $this->promotionUserRepo->find($id);
        if (!$promotionUser) {
            return null;
        }
        return $this->serializer->serialize($promotionUser, 'json', ['groups' => 'promotion_user:read']);
    }
}
