<?php

namespace App\Entity;

use App\Repository\RefundRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefundRepository::class)]
class Refund
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $refundedQuantity = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $refundDate = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeRefundId = null;

    #[ORM\ManyToOne(inversedBy: 'refunds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrderProduct $orderProduct = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefundedQuantity(): ?int
    {
        return $this->refundedQuantity;
    }

    public function setRefundedQuantity(int $refundedQuantity): static
    {
        $this->refundedQuantity = $refundedQuantity;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRefundDate(): ?\DateTimeImmutable
    {
        return $this->refundDate;
    }

    public function setRefundDate(\DateTimeImmutable $refundDate): static
    {
        $this->refundDate = $refundDate;

        return $this;
    }

    public function getStripeRefundId(): ?string
    {
        return $this->stripeRefundId;
    }

    public function setStripeRefundId(string $stripeRefundId): static
    {
        $this->stripeRefundId = $stripeRefundId;

        return $this;
    }

    public function getOrderProduct(): ?OrderProduct
    {
        return $this->orderProduct;
    }

    public function setOrderProduct(?OrderProduct $orderProduct): static
    {
        $this->orderProduct = $orderProduct;

        return $this;
    }
}
