<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $rq,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
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

            $plainPassword = $data['password'];
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $em->persist($user);
            $em->flush();

            // MAIL
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $signedUrl = $signatureComponents->getSignedUrl();

            
            $email = (new Email())
                ->from('test@test.com')
                ->to($user->getEmail())
                ->subject('Vérification de votre adresse email')
                ->html('<p>Merci pour votre inscription. Veuillez confirmer votre adresse email en cliquant sur le lien suivant : 
                <a href="' . $signedUrl . '">Confirmer mon compte</a></p>');

            $mailer->send($email);

            return new JsonResponse([
                'message' => 'Utilisateur enregistré avec succès. Un email de confirmation vous a été envoyé.',
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
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de l\'enregistrement',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/verify-email', name: 'verify_email', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        EntityManagerInterface $em,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $userRepo
    ): JsonResponse {
        $userId = $request->query->get('id');

        if (!$userId) {
            return new JsonResponse(['error' => 'Identifiant manquant, veuillez cliquer sur le lien dans l\email'], 400);
        }

        $user = $userRepo->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            return new JsonResponse([
                'error' => 'Le lien de vérification est invalide ou expiré',
            ], 400);
        }

        $user->setIsVerified(true);
        $em->flush();

        return new JsonResponse(['message' => 'Email vérifié avec succès !']);
    }
}
