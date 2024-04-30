<?php

namespace App\Entity;

use App\Repository\SalonPriveeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SalonPriveeRepository::class)]
class SalonPrivee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEspacesDeTravailPrives", "reservation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEspacesDeTravailPrives", "reservation"])]
    private ?string $nomSalonPrivee = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEspacesDeTravailPrives"])]
    private ?string $capacite = null;

    #[ORM\ManyToOne(inversedBy: 'salonPrivees')]
    #[Groups(["getEspacesDeTravailPrives"])]
    private ?EspaceDeTravail $espaceDeTravail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalonPrivee(): ?string
    {
        return $this->nomSalonPrivee;
    }

    public function setNomSalonPrivee(string $nomSalonPrivee): static
    {
        $this->nomSalonPrivee = $nomSalonPrivee;

        return $this;
    }

    public function getCapacite(): ?string
    {
        return $this->capacite;
    }

    public function setCapacite(string $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getEspaceDeTravail(): ?EspaceDeTravail
    {
        return $this->espaceDeTravail;
    }

    public function setEspaceDeTravail(?EspaceDeTravail $espaceDeTravail): static
    {
        $this->espaceDeTravail = $espaceDeTravail;

        return $this;
    }
}
