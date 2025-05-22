<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ImageController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ImageService $imageService,
        private readonly ImageRepository $imageRepo
    ) {}
    /*
        PUBLIC
    */
    #[Route('/api/image', name: 'get_images', methods: ['GET'])]
    public function getAllImages(): JsonResponse
    {
        try {
            $images = $this->imageService->getAllImages();
            return new JsonResponse($images, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la récupérations des images'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/image/{id}', name: 'get_one_image', methods: ['GET'])]
    public function getImageById(int $id): JsonResponse
    {
        try {
            $image = $this->imageService->getImageById($id);
            return new JsonResponse($image, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la récupération de l\'image'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/image/{id}', name: 'update_image', methods: ['PUT'])]
    public function updateImage(int $id, Request $rq): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);
            $image = $this->imageService->updateImage($id, $data);

            return new JsonResponse($image, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la mise à jour de l\'image'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(int $id): JsonResponse
    {
        try {
            $imageDeleted = $this->imageService->deleteImageFromTable($id);

            return match ($imageDeleted) {
                true => new JsonResponse(['message' => 'Image supprimé avec succès'], JsonResponse::HTTP_OK),
                'not_found' => new JsonResponse(['error' => 'Produit non trouvé'], JsonResponse::HTTP_NOT_FOUND),
                'has_category' => throw new AccessDeniedHttpException('L\'image ne peut pas être supprimée, car elle est associée à une catégorie.') 
            };
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la suppression de l\'image'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
