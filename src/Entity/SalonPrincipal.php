<?php

namespace App\Entity;

use App\Repository\SalonPrincipalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SalonPrincipalRepository::class)]
class SalonPrincipal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEspacesDeTravailPrincipale", "reservation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getEspacesDeTravailPrincipale", "reservation"])] 
    private ?string $nomSalonPrincipal = null;

    #[ORM\ManyToOne(inversedBy: 'salonPrincipals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EspaceDeTravail $espaceDeTravail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalonPrincipal(): ?string
    {
        return $this->nomSalonPrincipal;
    }

    public function setNomSalonPrincipal(string $nomSalonPrincipal): static
    {
        $this->nomSalonPrincipal = $nomSalonPrincipal;

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
