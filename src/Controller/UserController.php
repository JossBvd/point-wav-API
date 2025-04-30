<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /*
        ROLE_USER
    */
    #[Route('/api/user', name: 'get_me', methods: ['GET'])]
    public function getMe(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'birthday' => $user->getBirthday() ? $user->getBirthday()->format('Y-m-d') : null,
            'address' => $user->getAddress(),
            'registrationDate' => $user->getRegistrationDate()->format('Y-m-d H:i:s'),
            'isVerified' => $user->isVerified(),
            'isActive' => $user->isActive()
        ];
    
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/api/user', name: 'update_me', methods: ['PUT'])]
    public function updateMe(Request $rq, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($rq->getContent(), true);

        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }
        if (isset($data['birthday'])) {
            $user->setBirthday(new DateTimeImmutable($data['birthday']));
        }
        if (isset($data['address'])) {
            $user->setAddress($data['address']);
        }
    
        $em->flush();

        return new JsonResponse(['status' => 'Utilisateur modifié'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/user', name: 'delete_me', methods: ['DELETE'])]
    public function DeleteMe(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
        $user->setIsActive(false);

        $em->flush();

        return new JsonResponse(['status' => 'Utilisateur supprimé'], JsonResponse::HTTP_OK);
    }

    /*
        ROLE_ADMIN
    */ 
    #[Route('/api/admin/user', name: 'get_users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepo): JsonResponse
    {
        try {
            $users = $userRepo->findAll();
            if (!$users) {
                return new JsonResponse(['error' => 'Liste utilisateurs non trouvée'], JsonResponse::HTTP_NOT_FOUND);
            }

            $data = array_map(function (User $user) {
                return [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                    'birthday' => $user->getBirthday(),
                    'address' => $user->getAddress(),
                    'registrationDate' => $user->getRegistrationDate(),
                    'isVerified' => $user->isVerified(),
                    'isActive' => $user->isActive()

                ];
            }, $users);
            return new JsonResponse($data, JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la récupérations des utilisateurs'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/user/{id}', name: 'get_one_user', methods: ['GET'])]
    public function getOneUser(UserRepository $userRepo, int $id): JsonResponse
{
    $user = $userRepo->find($id);
    
    if (!$user) {
        return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
    }

    $data = [
        'id' => $user->getId(),
        'firstName' => $user->getFirstName(),
        'lastName' => $user->getLastName(),
        'email' => $user->getEmail(),
        'roles' => $user->getRoles(),
        'birthday' => $user->getBirthday(),
        'address' => $user->getAddress(),
        'registrationDate' => $user->getRegistrationDate()->format('Y-m-d H:i:s'),
        'isVerified' => $user->isVerified(),
        'isActive' => $user->isActive()
    ];

    return new JsonResponse($data, JsonResponse::HTTP_OK);
}
}
