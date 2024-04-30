<?php

namespace App\Entity;

use App\Repository\FormuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FormuleRepository::class)]
class Formule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["formule", "reservation"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["formule", "reservation"])]
    private ?string $nomFormule = null;

    #[ORM\Column(length: 255)]
    #[Groups(["formule", "reservation"])]
    private ?string $descriptionFormule = null;

    #[ORM\Column(length: 255)]
    #[Groups(["formule", "reservation"])]
    private ?string $prix = null;

    #[ORM\OneToMany(mappedBy: 'formule', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\Column(length: 255)]
    #[Groups(["formule", "reservation"])]
    private ?string $description2 = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomFormule(): ?string
    {
        return $this->nomFormule;
    }

    public function setNomFormule(string $nomFormule): self
    {
        $this->nomFormule = $nomFormule;

        return $this;
    }

    public function getDescriptionFormule(): ?string
    {
        return $this->descriptionFormule;
    }

    public function setDescriptionFormule(string $descriptionFormule): self
    {
        $this->descriptionFormule = $descriptionFormule;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setFormule($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getFormule() === $this) {
                $reservation->setFormule(null);
            }
        }

        return $this;
    }

    public function getDescription2(): ?string
    {
        return $this->description2;
    }

    public function setDescription2(string $description2): static
    {
        $this->description2 = $description2;

        return $this;
    }
}
