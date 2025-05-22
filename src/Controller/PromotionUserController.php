<?php
namespace App\Controller;

use App\Service\PromotionUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PromotionUserController extends AbstractController
{
    public function __construct(
        private readonly PromotionUserService $promotionUserService
    ) {}

    #[Route('/api/admin/promotion-user', name: 'get_promotion_user', methods: ['GET'])]
    public function getPromotions(): JsonResponse
    {
        try {
            $json = $this->promotionUserService->getAll();
            return JsonResponse::fromJsonString($json, JsonResponse::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération des promotions utilisées'], 500);
        }
    }

    #[Route('/api/admin/promotion-user/{id}', name: 'get_one_promotion_user', methods: ['GET'])]
    public function getOnePromotionUsed(int $id): JsonResponse
    {
        try {
            $json = $this->promotionUserService->getOne($id);
            if (!$json) {
                return new JsonResponse(['error' => 'Promotion utilisée non trouvée'], 404);
            }
            return JsonResponse::fromJsonString($json, JsonResponse::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération de la promotion utilisée'], 500);
        }
    }
}
