<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentController extends AbstractController
{
    #[Route('/api/payment/success/{orderId}', name: 'app_payment_success')]
    public function success(int $orderId, OrderRepository $orderRepo): JsonResponse
    {
        $order = $orderRepo->find($orderId);
        
        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }
        // $order->setIsPaid(true);
        return new JsonResponse('Paiement réussi. Merci pour votre commande !');

    }


    #[Route('/api/payment/cancel', name: 'app_payment_cancel')]
    public function cancel(): JsonResponse
    {
        return new JsonResponse('Paiement annulé. Vous pouvez réessayer.');
    }

    #[Route('/api/webhook', name: 'app_webhook_stripe', methods: ['POST'])]
    public function stripeWebhook(
        Request $request,
        StripeService $stripeService,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $endpointSecret = $this->getParameter('stripe.webhook_secret');

        try {
            $event = $stripeService->handleWebhook($payload, $sigHeader, $endpointSecret);

            // Traitement de l'événement de paiement réussi
            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $order = $orderRepository->findByStripeSessionId($session->id);

                if ($order) {
                    $order->setIsPaid(true);
                    $order->setPaidAt(new \DateTimeImmutable());
                    $order->setStripePaymentId($session->payment_intent);
                    $entityManager->flush();
                }
            }

            return new Response('Webhooks processed successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Webhook Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
