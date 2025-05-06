<?php

namespace App\Controller;

use App\Entity\PromotionUser;
use App\Repository\PromotionUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PromotionUserController extends AbstractController
{
    #[Route('/api/admin/promotion-user', name: 'get_promotion_user', methods: ['GET'])]
    public function getPromotions(PromotionUserRepository $promotionUserRepo): JsonResponse
    {
        try {
            $promotionsUsed = $promotionUserRepo->findAll();
            $data = array_map(function (PromotionUser $promotionUsed) {
                return  [
                    'id' => $promotionUsed->getId(),
                    'promotion_id' => $promotionUsed->getPromotion()->getId(),
                    'user_id' => $promotionUsed->getUser()->getId(),
                    'used_date' => $promotionUsed->getUsedDate()
                ];
            }, $promotionsUsed);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la liste des promotions utilisées'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/promotion-user/{id}', name: 'get_one_promotion_user', methods: ['GET'])]
    public function getOnePromotionUsed(PromotionUserRepository $promotionUserRepo, int $id): JsonResponse
    {
        try {
            $promotionUsed = $promotionUserRepo->find($id);
            return new JsonResponse([
                'id' => $promotionUsed->getId(),
                'promotion_id' => $promotionUsed->getPromotion()->getId(),
                'user_id' => $promotionUsed->getUser()->getId(),
                'used_date' => $promotionUsed->getUsedDate()
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la promotion utilisée'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
