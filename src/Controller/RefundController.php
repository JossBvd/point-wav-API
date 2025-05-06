<?php

namespace App\Controller;

use App\Entity\Refund;
use App\Repository\OrderProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RefundController extends AbstractController
{
    #[Route('/api/refund/{id}', name: 'create_refund', methods: ['POST'])]
    public function index(Request $rq, EntityManagerInterface $em, int $id, OrderProductRepository $orderProductRepo): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);
            $orderproduct = $orderProductRepo->find($id);
            if (!$orderproduct) {
                return new JsonResponse([
                    'error' => 'Aucun produit commandé selectionné'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $refund = new Refund();
            

            return new JsonResponse([
                'message' => 'Remboursement initialisé avec succès',
                'Remboursement' => [
                    'id' => $refund->getId(),
                    'order_product_id' => $refund->getOrderProduct(),
                    'refund_quantity' => $refund->getRefundedQuantity(),
                    'status' => $refund->getStatus(),
                    'refund_date' => $refund->getRefundDate(),
                    'stripe_refund_id' => $refund->getStripeRefundId(),
                ]
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de l\'initialisation du remboursement',

            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
