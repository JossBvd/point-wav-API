<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer
    ) {}

    #[Route('/api/request-reset-password', name: 'forgot_password_request', methods:['POST'])]
    public function request(Request $request, UserRepository $userRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';

        $user = $userRepo->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['message' => 'Si cet email existe, un lien de réinitialisation a été envoyé'], 200);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (TooManyPasswordRequestsException $e) {
            return new JsonResponse([
                'error:'.$e
            ], 500);
        }

        $emailMessage = (new TemplatedEmail())
            ->from('testreset@test.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken->getToken(),
                'resetUrl'   => sprintf('http://localhost:3000/reset-password/%s', $resetToken->getToken()),
            ]);

        $this->mailer->send($emailMessage);

        return new JsonResponse(['message' => 'Email envoyé si l’utilisateur existe.'], 200);

    }

    #[Route('/api/reset-password', name: 'reset_password', methods: ['POST'])]
    public function reset(  
        Request $request,
        ResetPasswordHelperInterface $helper,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token       = $data['token'] ?? '';
        $newPassword = $data['newPassword'] ?? '';

        try {
            $user = $helper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse(['message' => 'Token invalide ou expiré.'], 400);
        }

        $helper->removeResetRequest($token);

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $em->flush();

        return new JsonResponse(['message' => 'Mot de passe mis à jour.'], 200);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    // #[Route('/check-email', name: 'app_check_email')]
    // public function checkEmail(): Response
    // {
    //     // Generate a fake token if the user does not exist or someone hit this page directly.
    //     // This prevents exposing whether or not a user was found with the given email address or not
    //     if (null === ($resetToken = $this->getTokenObjectFromSession())) {
    //         $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
    //     }

    //     return $this->render('reset_password/check_email.html.twig', [
    //         'resetToken' => $resetToken,
    //     ]);
    // }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    // #[Route('/api/reset/{token}', name: 'reset_password')]
    // public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, ?string $token = null): Response
    // {
    //     if ($token) {
    //         // We store the token in session and remove it from the URL, to avoid the URL being
    //         // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
    //         $this->storeTokenInSession($token);

    //         return $this->redirectToRoute('app_reset_password');
    //     }

    //     $token = $this->getTokenFromSession();

    //     if (null === $token) {
    //         throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
    //     }

    //     try {
    //         /** @var User $user */
    //         $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
    //     } catch (ResetPasswordExceptionInterface $e) {
    //         $this->addFlash('reset_password_error', sprintf(
    //             '%s - %s',
    //             ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
    //             $e->getReason()
    //         ));

    //         return $this->redirectToRoute('app_forgot_password_request');
    //     }

    //     // The token is valid; allow the user to change their password.
    //     $form = $this->createForm(ChangePasswordForm::class);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         // A password reset token should be used only once, remove it.
    //         $this->resetPasswordHelper->removeResetRequest($token);

    //         /** @var string $plainPassword */
    //         $plainPassword = $form->get('plainPassword')->getData();

    //         // Encode(hash) the plain password, and set it.
    //         $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
    //         $this->entityManager->flush();

    //         // The session is cleaned up after the password has been changed.
    //         $this->cleanSessionAfterReset();

    //         return $this->redirectToRoute('app_home');
    //     }

    //     return $this->render('reset_password/reset.html.twig', [
    //         'resetForm' => $form,
    //     ]);
    // }

    // private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    // {
    //     $user = $this->entityManager->getRepository(User::class)->findOneBy([
    //         'email' => $emailFormData,
    //     ]);

    //     // Do not reveal whether a user account was found or not.
    //     if (!$user) {
    //         return $this->redirectToRoute('app_check_email');
    //     }

    //     try {
    //         $resetToken = $this->resetPasswordHelper->generateResetToken($user);
    //     } catch (ResetPasswordExceptionInterface $e) {
    //         // If you want to tell the user why a reset email was not sent, uncomment
    //         // the lines below and change the redirect to 'app_forgot_password_request'.
    //         // Caution: This may reveal if a user is registered or not.
    //         //
    //         // $this->addFlash('reset_password_error', sprintf(
    //         //     '%s - %s',
    //         //     ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE,
    //         //     $e->getReason()
    //         // ));

    //         return $this->redirectToRoute('app_check_email');
    //     }

    //     $email = (new TemplatedEmail())
    //         ->from(new Address('test@test.com', 'Point-Wav Bot'))
    //         ->to((string) $user->getEmail())
    //         ->subject('Your password reset request')
    //         ->htmlTemplate('reset_password/email.html.twig')
    //         ->context([
    //             'resetToken' => $resetToken,
    //         ]);

    //     $mailer->send($email);

    //     // Store the token object in session for retrieval in check-email route.
    //     $this->setTokenObjectInSession($resetToken);

    //     return $this->redirectToRoute('app_check_email');
    // }
}
