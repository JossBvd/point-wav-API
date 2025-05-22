<?php
namespace App\Service;

use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class PromotionService
{
    public function __construct(
        private PromotionRepository $promotionRepo,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {}

    public function getAll(): string
    {
        $promotions = $this->promotionRepo->findAll();
        return $this->serializer->serialize($promotions, 'json', ['groups' => ['promotion:read']]);
    }

    public function getOne(int $id): ?string
    {
        $promotion = $this->promotionRepo->find($id);
        return $promotion
            ? $this->serializer->serialize($promotion, 'json', ['groups' => ['promotion:read']])
            : null;
    }

    public function create(Request $request): string
    {
        $data = json_decode($request->getContent(), true);

        $promotion = new Promotion();
        $promotion->setName($data['name']);
        $promotion->setCode($data['code']);
        $promotion->setReductionType($data['reductionType']);
        $promotion->setReductionValue($data['reductionValue']);
        $promotion->setStartDate(new \DateTimeImmutable($data['startDate']));
        $promotion->setEndDate(new \DateTimeImmutable($data['endDate']));
        $promotion->setIsActive($promotion->isCurrentlyActive());
        $promotion->setRegistrationDate(new \DateTimeImmutable());

        $this->em->persist($promotion);
        $this->em->flush();

        return $this->serializer->serialize($promotion, 'json', ['groups' => ['promotion:read']]);
    }

    public function update(Request $request, int $id): ?string
    {
        $promotion = $this->promotionRepo->find($id);
        if (!$promotion) return null;

        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) $promotion->setName($data['name']);
        if (isset($data['startDate'])) $promotion->setStartDate(new \DateTimeImmutable($data['startDate']));
        if (isset($data['endDate'])) $promotion->setEndDate(new \DateTimeImmutable($data['endDate']));
        if (isset($data['isActive'])) $promotion->setIsActive($data['isActive']);

        $this->em->flush();

        return $this->serializer->serialize($promotion, 'json', ['groups' => ['promotion:read']]);
    }

    public function delete(int $id): ?string
    {
        $promotion = $this->promotionRepo->find($id);
        if (!$promotion) return null;

        if ($promotion->getOrders() && !$promotion->getOrders()->isEmpty()) {
            throw new \Exception('Promotion utilisée dans une commande.');
        }

        $this->em->remove($promotion);
        $this->em->flush();

        return $promotion->getId();
    }
}
