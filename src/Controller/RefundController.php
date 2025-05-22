<?php

namespace App\Controller;

use App\Service\RefundService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RefundController extends AbstractController
{
    public function __construct(private readonly RefundService $refundService) {}

    #[Route('/api/refund/{id}', name: 'create_refund', methods: ['POST'])]
    public function create(Request $rq, int $id): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);
            if ($data === null) {
                return new JsonResponse([
                    'error' => 'JSON invalide ou vide'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $result = $this->refundService->createRefund($data, $id);

            if (isset($result['error'])) {
                return new JsonResponse(['error' => $result['error']], $result['status']);
            }

            return new JsonResponse($result, JsonResponse::HTTP_OK, [], true);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de l\'initialisation du remboursement' . $e
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/refund', name: 'get_all_refunds', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        try {
            $refunds = $this->refundService->getAllRefunds();
            return new JsonResponse($refunds, JsonResponse::HTTP_OK, [], true);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la liste des remboursements'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/refund/{id}', name: 'get_one_refund', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        try {
            $refund = $this->refundService->getOneRefund($id);

            if (isset($result['error'])) {
                return new JsonResponse(['error' => $result['error']], $result['status']);
            }

            return new JsonResponse($refund, JsonResponse::HTTP_OK, [], true);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération du remboursement' . $e
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/refund/{id}', name: 'update_refund', methods: ['PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return new JsonResponse(['error' => 'JSON invalide ou vide'], 400);
            }

            $refund = $this->refundService->updateRefund($id, $data);

            if (isset($result['error'])) {
                return new JsonResponse(['error' => $result['error']], $result['status']);
            }

            return new JsonResponse($refund, JsonResponse::HTTP_OK, [], true);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour du remboursement'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
