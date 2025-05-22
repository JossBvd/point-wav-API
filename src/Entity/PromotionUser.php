<?php

namespace App\Entity;

use App\Repository\PromotionUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PromotionUserRepository::class)]
class PromotionUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('promotion_user:read')]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups('promotion_user:read')]
    private ?\DateTimeImmutable $usedDate = null;

    #[ORM\ManyToOne(inversedBy: 'promotionUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('promotion_user:read')]
    private ?Promotion $promotion = null;

    #[ORM\ManyToOne(inversedBy: 'promotionUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('promotion_user:read')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsedDate(): ?\DateTimeImmutable
    {
        return $this->usedDate;
    }

    public function setUsedDate(\DateTimeImmutable $usedDate): static
    {
        $this->usedDate = $usedDate;

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): static
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
