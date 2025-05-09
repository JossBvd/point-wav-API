<?php
// src/Service/UserService.php

namespace App\Service;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepo,
        private readonly SerializerInterface $serializer,
        private readonly Security $security
    ) {}

    public function getMe(): ?string
    {
        $user = $this->security->getUser();
        return $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
    }

    public function updateMe(array $data): ?string
    {
        $user = $this->security->getUser();

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

        $this->em->flush();

        return $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
    }

    public function deleteMe(): bool
    {
        $user = $this->security->getUser();
        if (!$user) {
            return false;
        }

        $user->setIsActive(false);
        $this->em->flush();
        return true;
    }

    public function getAllUsers(): string
    {
        $users = $this->userRepo->findAll();
        return $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);
    }

    public function getUserById(int $id): ?string
    {
        $user = $this->userRepo->find($id);
        return $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);
    }
}
