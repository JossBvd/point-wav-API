<?php

namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(private readonly CategoryService $categoryService) {}

    /*
        PUBLIC_ACCESS
    */

    #[Route('/api/category', name: 'get_categories', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();
        return new JsonResponse($categories, JsonResponse::HTTP_OK, [], true);
    }

    /*
        ROLE_ADMIN
    */

    #[Route('/api/admin/category', name: 'add_category', methods: ['POST'])]
    public function createCategory(Request $request): JsonResponse
    {
        try {
            $name = $request->request->get('name');
            $uploadedImage = $request->files->get('image');

            if (!$name || !$uploadedImage) {
                return new JsonResponse(['error' => 'Nom ou image manquant'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $category = $this->categoryService->createCategory($name, $uploadedImage);

            return new JsonResponse($category, JsonResponse::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la création'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/category/{id}', name: 'update_category', methods: ['POST'])]
    public function updateCategory(Request $request, int $id): JsonResponse
    {
        try {
            $name = $request->get('name');
            $uploadedImage = $request->files->get('image');

            $updatedCategory = $this->categoryService->updateCategory($id, $name, $uploadedImage);

            if (!$updatedCategory) {
                return new JsonResponse(['error' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }

            return new JsonResponse($updatedCategory, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour de la catégorie'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/category/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $result = $this->categoryService->deleteCategory($id);

            return match ($result) {
                true => new JsonResponse(['message' => 'Catégorie supprimée avec succès'], JsonResponse::HTTP_OK),
                'not_found' => new JsonResponse(['error' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND),
                'has_products' => new JsonResponse(['error' => 'La catégorie contient des produits'], JsonResponse::HTTP_FORBIDDEN)
            };
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression de la catégorie'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
