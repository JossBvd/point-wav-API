<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PromotionController extends AbstractController
{
    /* 
        ROLE_ADMIN
    */
    #[Route('/api/admin/promotion', name: 'get_promotions', methods: ['GET'])]
    public function getPromotions(PromotionRepository $promotionRepo): JsonResponse
    {
        try {
            $promotions = $promotionRepo->findAll();
            $data = array_map(function (Promotion $promotion) {
                return  [
                    'id' => $promotion->getId(),
                    'name' => $promotion->getName(),
                    'code' => $promotion->getCode(),
                    'reduction_type' => $promotion->getReductionType(),
                    'reduction_value' => $promotion->getReductionValue(),
                    'start_date' => $promotion->getStartDate(),
                    'end_date' => $promotion->getEndDate(),
                    'registrationDate' => $promotion->getRegistrationDate(),
                    'isActive' => $promotion->isActive()
                ];
            }, $promotions);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la liste des promotions'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/promotion/{id}', name: 'get_one_promotion', methods: ['GET'])]
    public function getOnePromotion(PromotionRepository $promotionRepo, int $id): JsonResponse
    {
        try {
            $promotion = $promotionRepo->find($id);
            return new JsonResponse([
                'id' => $promotion->getId(),
                'name' => $promotion->getName(),
                'code' => $promotion->getCode(),
                'reduction_type' => $promotion->getReductionType(),
                'reduction_value' => $promotion->getReductionValue(),
                'start_date' => $promotion->getStartDate(),
                'end_date' => $promotion->getEndDate(),
                'registrationDate' => $promotion->getRegistrationDate(),
                'isActive' => $promotion->isActive()
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la promotion'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/promotion/{id}', name: 'update_promotion', methods: ['PATCH'])]
    public function updateProduct(Request $rq, EntityManagerInterface $em, PromotionRepository $promotionRepo, int $id): JsonResponse
    {
        try {
            $promotion = $promotionRepo->find($id);
            $data = json_decode($rq->getContent(), true);

            if (!$promotion) {
                return new JsonResponse(['error' => 'Promotion non trouvé'], JsonResponse::HTTP_NOT_FOUND);
            }

            if (isset($data['name'])) {
                $promotion->setName($data['name']);
            }

            if (isset($data['startDate'])) {
                $promotion->setStartDate(new \DateTimeImmutable($data['startDate']));
            }

            if (isset($data['endDate'])) {
                $promotion->setEndDate(new \DateTimeImmutable($data['endDate']));
            }

            if (isset($data['isActive'])) {
                $promotion->setIsActive($data['isActive']);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Promotion modifié avec succès',
                'Promotion' => [
                    'id' => $promotion->getId(),
                    'name' => $promotion->getName(),
                    'code' => $promotion->getCode(),
                    'reduction_type' => $promotion->getReductionType(),
                    'reduction_value' => $promotion->getReductionValue(),
                    'start_date' => $promotion->getStartDate(),
                    'end_date' => $promotion->getEndDate(),
                    'registrationDate' => $promotion->getRegistrationDate(),
                    'isActive' => $promotion->isActive()
                ]
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour de la promotion'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/promotion/{id}', name: 'delete_promotion', methods: ['DELETE'])]
    public function deletePromotion(EntityManagerInterface $em, PromotionRepository $promotionRepo, int $id): JsonResponse
    {
        try {
            $promotion = $promotionRepo->find($id);

            if ($promotion->getOrders() && !$promotion->getOrders()->isEmpty()) {
                return new JsonResponse([
                    'error' => 'Impossible de supprimer la promotion car elle a déjà été utilisée pour une commande'
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            $em->remove($promotion);
            $em->flush();

            return new JsonResponse([
                'message' => 'Promotion supprimée avec succès'
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la suppression de la promotion'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/admin/promotion', name: 'create_promotion', methods: ['POST'])]
    public function index(Request $rq, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);

            $promotion = new Promotion();
            $promotion->setName($data['name']);
            $promotion->setCode($data['code']);
            $promotion->setReductionType($data['reductionType']);
            $promotion->setReductionValue($data['reductionValue']);
            $promotion->setStartDate(new \DateTimeImmutable($data['startDate']));
            $promotion->setEndDate(new \DateTimeImmutable($data['endDate']));
            $promotion->setIsActive($promotion->isCurrentlyActive());
            $promotion->setRegistrationDate(new \DateTimeImmutable());

            $em->persist($promotion);
            $em->flush();

            return new JsonResponse([
                'message' => 'Promotion ajoutée avec succès',
                'promotion' => [
                    'id' => $promotion->getId(),
                    'name' => $promotion->getName(),
                    'code' => $promotion->getCode(),
                    'reduction_type' => $promotion->getReductionType(),
                    'reduction_value' => $promotion->getReductionValue(),
                    'start_date' => $promotion->getStartDate(),
                    'end_date' => $promotion->getEndDate(),
                    'registrationDate' => $promotion->getRegistrationDate(),
                    'isActive' => $promotion->isActive()
                ]
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la création d\'une promotion',

            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
