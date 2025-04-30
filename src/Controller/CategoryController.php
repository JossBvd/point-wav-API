<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    /*
        PUBLIC_ACCESS
    */

    #[Route('/api/categories', name: 'get_categories', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepo): JsonResponse
    {
        $categories = $categoryRepo->findAll();

        $data = array_map(function (Category $category) {
            return [
                'id' => $category->getId(),
                'firstName' => $category->getName()
            ];
        }, $categories);
    
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /*
        ROLE_ADMIN
    */

    #[Route('/api/admin/category', name: 'add_category', methods: ['POST'])]
    public function createCategory(Request $rq, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);
            
            $category = new Category();
            $category->setName($data['name']);

            $em->persist($category);
            $em->flush();

            return new JsonResponse([
                'message' => 'Catégorie enregistrée avec succès',
                'categorie' => [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ],
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => "Une erreur est survenue lors de la création d'une catégorie"], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
