<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'order:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read'])]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $birthday = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $address = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $registrationDate = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isActive = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isVerified = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @var Collection<int, promotion>
     */
    #[ORM\ManyToMany(targetEntity: Promotion::class, inversedBy: 'users')]
    private Collection $promotions;

    /**
     * @var Collection<int, PromotionUser>
     */
    #[ORM\OneToMany(targetEntity: PromotionUser::class, mappedBy: 'user')]
    private Collection $promotionUsers;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->promotionUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeImmutable $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, promotions>
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotions(promotion $promotions): static
    {
        if (!$this->promotions->contains($promotions)) {
            $this->promotions->add($promotions);
        }

        return $this;
    }

    public function removePromotions(promotion $promotions): static
    {
        $this->promotions->removeElement($promotions);

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
            $promotionUser->setUser($this);
        }

        return $this;
    }

    public function removePromotionUser(PromotionUser $promotionUser): static
    {
        if ($this->promotionUsers->removeElement($promotionUser)) {
            // set the owning side to null (unless already changed)
            if ($promotionUser->getUser() === $this) {
                $promotionUser->setUser(null);
            }
        }

        return $this;
    }
}