<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read','promotion:read', 'promotion_user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['promotion:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['promotion:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Groups(['promotion:read'])]
    private ?string $reductionType = null;

    #[ORM\Column]
    #[Groups(['promotion:read'])]
    private ?int $reductionValue = null;

    #[ORM\Column]
    #[Groups(['promotion:read'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column]
    #[Groups(['promotion:read'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    #[Groups(['promotion:read'])]
    private ?bool $isActive = null;

    #[ORM\Column]
    #[Groups(['promotion:read'])]
    private ?\DateTimeImmutable $registrationDate = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'promotion')]
    private Collection $orders;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'promotions')]
    private Collection $users;

    /**
     * @var Collection<int, PromotionUser>
     */
    #[ORM\OneToMany(targetEntity: PromotionUser::class, mappedBy: 'promotion')]
    private Collection $promotionUsers;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->promotionUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getReductionType(): ?string
    {
        return $this->reductionType;
    }

    public function setReductionType(string $reductionType): static
    {
        $this->reductionType = $reductionType;

        return $this;
    }

    public function getReductionValue(): ?int
    {
        return $this->reductionValue;
    }

    public function setReductionValue(int $reductionValue): static
    {
        $this->reductionValue = $reductionValue;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isCurrentlyActive(): bool
    {
        $now = new \DateTime();
        return $this->getStartDate() <= $now && $this->getEndDate() >= $now;
    }


    public function getRegistrationDate(): ?\DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeImmutable $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setPromotion($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getPromotion() === $this) {
                $order->setPromotion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addPromotions($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removePromotions($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, PromotionUser>
     */
    public function getPromotionUsers(): Collection
    {
        return $this->promotionUsers;
    }

    public function addPromotionUser(PromotionUser $promotionUser): static
    {
        if (!$this->promotionUsers->contains($promotionUser)) {
            $this->promotionUsers->add($promotionUser);
            $promotionUser->setPromotion($this);
        }

        return $this;
    }

    public function removePromotionUser(PromotionUser $promotionUser): static
    {
        if ($this->promotionUsers->removeElement($promotionUser)) {
            // set the owning side to null (unless already changed)
            if ($promotionUser->getPromotion() === $this) {
                $promotionUser->setPromotion(null);
            }
        }

        return $this;
    }
}
