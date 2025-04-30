<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ImageController extends AbstractController
{
    /*
        PUBLIC
    */
    #[Route('/api/image', name: 'get_images', methods: ['GET'])]
    public function getImages(ImageRepository $imageRepo): JsonResponse
    {
        try {
            $images = $imageRepo->findAll();
            if (!$images) {
                return new JsonResponse(['error' => 'Liste utilisateurs non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }

            $data = array_map(function (Image $image) {
                return [
                    'id' => $image->getId(),
                    'url' => $image->getUrl(),
                    'ranking' => $image->getRanking(),
                    'product_id' => $image->getProduct(),
                ];
            }, $images);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la récupérations des images'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /*
        ROLE_ADMIN
    */
    #[Route('/api/admin/image', name: 'add_image', methods: ['POST'])]
    public function addImage(Request $rq, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);

            $image = new Image();
            $image->setUrl($data['url']);
            $image->setRanking($data['ranking']);

            $em->persist($image);
            $em->flush();

            return new JsonResponse([
                'message' => 'Image enregistrée avec succès',
                'image' => [
                    'id' => $image->getId(),
                    'url' => $image->getUrl(),
                    'ranking' => $image->getRanking(),
                    'product_id' => $image->getProduct()
                ],
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de l\'ajout de l\'image'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/image/{id}', name: 'update_image', methods: ['PUT'])]
    public function updateImage(ImageRepository $imageRepo, EntityManagerInterface $em, int $id, Request $rq): JsonResponse
    {
        $image = $imageRepo->find($id);
        if (!$image) {
            return new JsonResponse(['error' => 'Image non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($rq->getContent(), true);

        if (isset($data['url'])) {
            $image->setUrl($data['url']);
        }
        if (isset($data['ranking'])) {
            $image->setRanking($data['ranking']);
        }
        if (isset($data['product_id'])) {
            $image->setProduct($data['product_id']);
        }

        $em->flush();

        return new JsonResponse([
            'status' => 'Image modifiée',
            'Image' => [
                'id' => $image->getId(),
                'url' => $image->getUrl(),
                'ranking' => $image->getRanking(),
                'product_id' => $image->getProduct()
            ]
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/api/admin/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(ImageRepository $imageRepo, EntityManagerInterface $em, int $id): JsonResponse
    {
        $image = $imageRepo->find($id);
        if (!$image) {
            return new JsonResponse(['error' => 'Image non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
        $em->remove($image);
        $em->flush();

        return new JsonResponse(['status' => 'Image supprimée'], JsonResponse::HTTP_OK);
    }
}
