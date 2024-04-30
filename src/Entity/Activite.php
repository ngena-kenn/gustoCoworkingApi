<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse_ip = null;

    #[ORM\Column(length: 255)]
    private ?string $user_agent = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'user')]
    private ?self $activite = null;

    #[ORM\OneToMany(mappedBy: 'activite', targetEntity: self::class)]
    private Collection $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresse_ip;
    }

    public function setAdresseIp(string $adresse_ip): self
    {
        $this->adresse_ip = $adresse_ip;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }

    public function setUserAgent(string $user_agent): self
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    public function getActivite(): ?self
    {
        return $this->activite;
    }

    public function setActivite(?self $activite): self
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(self $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setActivite($this);
        }

        return $this;
    }

    public function removeUser(self $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getActivite() === $this) {
                $user->setActivite(null);
            }
        }

        return $this;
    }
}
