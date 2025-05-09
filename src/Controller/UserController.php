<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Service\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService) {}

    #[Route('/api/user', name: 'get_me', methods: ['GET'])]
    public function getMe(): JsonResponse
    {
        try {
            $user = $this->userService->getMe();
            return new JsonResponse($user, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la liste des utilisateurs'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user', name: 'update_me', methods: ['PUT'])]
    public function updateMe(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->userService->updateMe($data);

            return new JsonResponse($user, JsonResponse::HTTP_OK, [], true);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour de l\'utilisateur'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user', name: 'delete_me', methods: ['DELETE'])]
    public function deleteMe(): JsonResponse
    {
        try {
            $userDeleted = $this->userService->deleteMe();
            return match ($userDeleted) {
                true => new JsonResponse(['message' => 'Utilisateur supprimé avec succès'], JsonResponse::HTTP_OK),
                false => new JsonResponse(['error' => 'Impossible de récupérer l\'utilisateur'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR)
            };
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la suppression de l\'utilisateur'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/user', name: 'get_users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers();
            return new JsonResponse($users, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération des utilisateurs'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/user/{id}', name: 'get_one_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            return new JsonResponse($user, JsonResponse::HTTP_OK, [], true);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération de l\'utilisateur'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
