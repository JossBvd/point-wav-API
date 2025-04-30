<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $rq, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($rq->getContent(), true);
            
            $user = new User();

            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setEmail($data['email']);
            $birthday = new DateTimeImmutable($data['birthday']);
            $user->setBirthday($birthday);
            $user->setAddress($data['address']);
            $user->setRegistrationDate(new DateTimeImmutable());
            $user->setIsActive(true);
            $user->setIsVerified(false);
            $user->setRoles(['ROLE_USER']);


            /** @var string $plainPassword */
            $plainPassword = $data['password'];
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            // var_dump($user); die();
            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'message' => 'Utilisateur enregistré avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'email' => $user->getEmail(),
                    'birthday' => $user->getBirthday()->format('Y-m-d'),
                    'address' => $user->getAddress(),
                    'registrationDate' => $user->getRegistrationDate()->format('Y-m-d H:i:s'),
                    'isActive' => $user->isActive(),
                    'isVerified' => $user->isVerified(),
                    'role' => $user->getRoles()
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de l\'enregistrement'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}