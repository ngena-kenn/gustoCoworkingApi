<?php

namespace App\Entity;

use App\Repository\EspaceDeTravailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EspaceDeTravailRepository::class)]
class EspaceDeTravail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getEspacesDeTravailPrives", "getEspacesDeTravailPrincipale"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'espaceDeTravail', targetEntity: SalonPrincipal::class)]
    #[Groups(["reservation"])]
    private Collection $salonPrincipals;

    #[ORM\OneToMany(mappedBy: 'espaceDeTravail', targetEntity: SalonPrivee::class)]
    #[Groups(["reservation"])]
    private Collection $salonPrivees;

    #[ORM\OneToMany(mappedBy: 'espacedetravail', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->salonPrincipals = new ArrayCollection();
        $this->salonPrivees = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, SalonPrincipal>
     */
    public function getSalonPrincipals(): Collection
    {
        return $this->salonPrincipals;
    }

    public function addSalonPrincipal(SalonPrincipal $salonPrincipal): static
    {
        if (!$this->salonPrincipals->contains($salonPrincipal)) {
            $this->salonPrincipals->add($salonPrincipal);
            $salonPrincipal->setEspaceDeTravail($this);
        }

        return $this;
    }

    public function removeSalonPrincipal(SalonPrincipal $salonPrincipal): static
    {
        if ($this->salonPrincipals->removeElement($salonPrincipal)) {
            // set the owning side to null (unless already changed)
            if ($salonPrincipal->getEspaceDeTravail() === $this) {
                $salonPrincipal->setEspaceDeTravail(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SalonPrivee>
     */
    public function getSalonPrivees(): Collection
    {
        return $this->salonPrivees;
    }

    public function addSalonPrivee(SalonPrivee $salonPrivee): static
    {
        if (!$this->salonPrivees->contains($salonPrivee)) {
            $this->salonPrivees->add($salonPrivee);
            $salonPrivee->setEspaceDeTravail($this);
        }

        return $this;
    }

    public function removeSalonPrivee(SalonPrivee $salonPrivee): static
    {
        if ($this->salonPrivees->removeElement($salonPrivee)) {
            // set the owning side to null (unless already changed)
            if ($salonPrivee->getEspaceDeTravail() === $this) {
                $salonPrivee->setEspaceDeTravail(null);
            }
        }

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
            $reservation->setEspacedetravail($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getEspacedetravail() === $this) {
                $reservation->setEspacedetravail(null);
            }
        }

        return $this;
    }
}
