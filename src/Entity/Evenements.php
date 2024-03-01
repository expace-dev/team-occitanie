<?php

namespace App\Entity;

use App\Repository\EvenementsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvenementsRepository::class)]
class Evenements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $visuel = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    private ?Users $auteur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEvents = null;

    #[ORM\Column(length: 255)]
    private ?string $typeSession = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisuel(): ?string
    {
        return $this->visuel;
    }

    public function setVisuel(string $visuel): static
    {
        $this->visuel = $visuel;

        return $this;
    }

    public function getAuteur(): ?Users
    {
        return $this->auteur;
    }

    public function setAuteur(?Users $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDateEvents(): ?\DateTimeInterface
    {
        return $this->dateEvents;
    }

    public function setDateEvents(\DateTimeInterface $dateEvents): static
    {
        $this->dateEvents = $dateEvents;

        return $this;
    }

    public function getTypeSession(): ?string
    {
        return $this->typeSession;
    }

    public function setTypeSession(string $typeSession): static
    {
        $this->typeSession = $typeSession;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
