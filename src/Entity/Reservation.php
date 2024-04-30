<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["reservation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["reservation"])]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    #[Groups(["reservation"])]
    private ?string $effectif = null;

    #[ORM\Column]
    #[Groups(["reservation"])]
    private ?bool $status = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    // #[Groups(["reservation"])]
    private ?User $User = null;

    #[ORM\Column(length: 255)]
    #[Groups(["reservation"])]
    private ?string $heureDeDebut = null;

    #[ORM\Column(length: 255)]
    #[Groups(["reservation"])]
    private ?string $heureDeFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["reservation"])]
    private ?string $prixReservation = null;

    #[ORM\OneToOne(mappedBy: 'reservation', cascade: ['persist', 'remove'])]
    #[Groups(["reservation"])]
    private ?Paiement $paiement = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[Groups(["reservation"])]
    private ?Formule $formule = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[Groups(["reservation"])]
    private ?EspaceDeTravail $espacedetravail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getEffectif(): ?string
    {
        return $this->effectif;
    }

    public function setEffectif(string $effectif): self
    {
        $this->effectif = $effectif;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getHeureDeDebut(): ?string
    {
        return $this->heureDeDebut;
    }

    public function setHeureDeDebut(string $heureDeDebut): static
    {
        $this->heureDeDebut = $heureDeDebut;

        return $this;
    }

    public function getHeureDeFin(): ?string
    {
        return $this->heureDeFin;
    }

    public function setHeureDeFin(string $heureDeFin): static
    {
        $this->heureDeFin = $heureDeFin;

        return $this;
    }

    public function getPrixReservation(): ?string
    {
        return $this->prixReservation;
    }

    public function setPrixReservation(?string $prixReservation): static
    {
        $this->prixReservation = $prixReservation;

        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(Paiement $paiement): static
    {
        // set the owning side of the relation if necessary
        if ($paiement->getReservation() !== $this) {
            $paiement->setReservation($this);
        }

        $this->paiement = $paiement;

        return $this;
    }

    public function getFormule(): ?Formule
    {
        return $this->formule;
    }

    public function setFormule(?Formule $formule): static
    {
        $this->formule = $formule;

        return $this;
    }

    public function getEspacedetravail(): ?EspaceDeTravail
    {
        return $this->espacedetravail;
    }

    public function setEspacedetravail(?EspaceDeTravail $espacedetravail): static
    {
        $this->espacedetravail = $espacedetravail;

        return $this;
    }
}
