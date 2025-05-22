<?php
namespace App\Controller;

use App\Service\PromotionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PromotionController extends AbstractController
{
    public function __construct(private PromotionService $promotionService) {}

    #[Route('/api/admin/promotion', name: 'get_promotions', methods: ['GET'])]
    public function getPromotions(): JsonResponse
    {
        try {
            return new JsonResponse($this->promotionService->getAll(), JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération des promotions'], 500);
        }
    }

    #[Route('/api/admin/promotion/{id}', name: 'get_one_promotion', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        try {
            $result = $this->promotionService->getOne($id);
            if (!$result) return new JsonResponse(['error' => 'Promotion non trouvée'], 404);
            return new JsonResponse($result, 200, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur'], 500);
        }
    }

    #[Route('/api/admin/promotion', name: 'create_promotion', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $result = $this->promotionService->create($request);
            return new JsonResponse($result, 201, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur création'], 500);
        }
    }

    #[Route('/api/admin/promotion/{id}', name: 'update_promotion', methods: ['PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->promotionService->update($request, $id);
            if (!$result) return new JsonResponse(['error' => 'Non trouvé'], 404);
            return new JsonResponse($result, 200, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur mise à jour'], 500);
        }
    }

    #[Route('/api/admin/promotion/{id}', name: 'delete_promotion', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->promotionService->delete($id);
            return new JsonResponse(['message' => 'Supprimé avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }
    }
}
