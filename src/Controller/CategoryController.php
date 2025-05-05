<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Image;
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

    #[Route('/api/category', name: 'get_categories', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepo): JsonResponse
    {
        $categories = $categoryRepo->findAll();

        $data = array_map(function (Category $category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'image' => $category->getImage()->getUrl()
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

            $name = $rq->request->get('name');
            $uploadedImage = $rq->files->get('image');

            // Vérification du type de fichier
            $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            if (!in_array($uploadedImage->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse(['error' => 'Type de fichier non supporté'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $filename = uniqid('img_') . '.' . $uploadedImage->guessExtension();

            $uploadedImage->move($this->getParameter('upload_image_directory'), $filename);

            $image = new Image();
            $image->setUrl('/uploads/images/' . $filename);
            $image->setRanking(1);


            $category = new Category();
            $category->setName($name);
            $category->setImage($image);

            $em->persist($image);
            $em->persist($category);
            $em->flush();

            return new JsonResponse([
                'message' => 'Catégorie créée avec succès',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'image' => $image->getUrl()
                ]
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la création'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Attention: Method POST car on envoi une image, Multipart en methode PUT est mal géré par symfony
    #[Route('/api/admin/category/{id}', name: 'update_category', methods: ['POST'])]
    public function updateCategory(Request $rq, EntityManagerInterface $em, CategoryRepository $categoryRepo ,int $id): JsonResponse
    {
        try {
            $category = $categoryRepo->find($id);

            if (!$category) {
                return new JsonResponse(['error' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }

            $name = $rq->get('name');
            if ($name) {
                $category->setName($name);
            }

            $uploadedImage = $rq->files->get('image');

            if ($uploadedImage) {
                $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

                if (!in_array($uploadedImage->getMimeType(), $allowedMimeTypes)) {
                    return new JsonResponse(['error' => 'Type de fichier non supporté'], JsonResponse::HTTP_BAD_REQUEST);
                }

                $filename = uniqid('img_') . '.' . $uploadedImage->guessExtension();
                $uploadDir = $this->getParameter('upload_image_directory');

                $uploadedImage->move($uploadDir, $filename);

                $oldImage = $category->getImage();
                if ($oldImage) {
                    $oldImagePath = $uploadDir . '/' . basename($oldImage->getUrl());
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $em->remove($oldImage);
                }

                $newImage = new Image();
                $newImage->setUrl('/uploads/images/' . $filename);
                $newImage->setRanking(1);

                $category->setImage($newImage);
                $newImage->setCategory($category);
                $em->persist($newImage);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Catégorie mise à jour avec succès',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'image' => $category->getImage()->getUrl()
                ]
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour de la catégorie'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/category/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function deleteCategory(EntityManagerInterface $em, CategoryRepository $categoryRepo ,int $id): JsonResponse
    {
        try {
            $category = $categoryRepo->find($id);

            if (!$category) {
                return new JsonResponse(['error' => 'Catégorie non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }

            if (!$category->getProducts()->isEmpty()) {
                return new JsonResponse(['error' => 'La catégorie ne peux pas être supprimé car elle contient des produits'], JsonResponse::HTTP_FORBIDDEN);
            }

            $uploadDir = $this->getParameter('upload_image_directory');

            $image = $category->getImage();
            if ($image) {
                $imagePath = $uploadDir . '/' . basename($image->getUrl());
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $em->remove($image);
            }
            $em->remove($category);
            $em->flush();

            return new JsonResponse([
                'message' => 'Catégorie supprimée avec succès'
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la suppression de la catégorie'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
