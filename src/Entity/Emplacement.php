<?php

namespace App\Entity;

use App\Repository\EmplacementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EmplacementRepository::class)]
class Emplacement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCapacite"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCapacite"])]
    private ?string $capacite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getCapacite"])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'emplacement', targetEntity: Table::class)]
    #[Groups(["getCapacite"])]
    private Collection $tables;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getCapacite"])]
    private ?string $typeEmplacement = null;

    public function __construct()
    {
        $this->tables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacite(): ?string
    {
        return $this->capacite;
    }

    public function setCapacite(string $capacite): self
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Table>
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(Table $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables->add($table);
            $table->setEmplacement($this);
        }

        return $this;
    }

    public function removeTable(Table $table): self
    {
        if ($this->tables->removeElement($table)) {
            // set the owning side to null (unless already changed)
            if ($table->getEmplacement() === $this) {
                $table->setEmplacement(null);
            }
        }

        return $this;
    }

    public function getTypeEmplacement(): ?string
    {
        return $this->typeEmplacement;
    }

    public function setTypeEmplacement(?string $typeEmplacement): self
    {
        $this->typeEmplacement = $typeEmplacement;

        return $this;
    }
}
