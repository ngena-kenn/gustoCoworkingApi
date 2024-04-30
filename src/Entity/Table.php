<?php

namespace App\Entity;

use App\Repository\TableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TableRepository::class)]
#[ORM\Table(name: '`table`')]
class Table
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nonTable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $capaciteTable = null;

    #[ORM\ManyToOne(inversedBy: 'tables')]
    private ?Emplacement $emplacement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNonTable(): ?string
    {
        return $this->nonTable;
    }

    public function setNonTable(?string $nonTable): self
    {
        $this->nonTable = $nonTable;

        return $this;
    }

    public function getCapaciteTable(): ?string
    {
        return $this->capaciteTable;
    }

    public function setCapaciteTable(?string $capaciteTable): self
    {
        $this->capaciteTable = $capaciteTable;

        return $this;
    }

    public function getEmplacement(): ?Emplacement
    {
        return $this->emplacement;
    }

    public function setEmplacement(?Emplacement $emplacement): self
    {
        $this->emplacement = $emplacement;

        return $this;
    }
}
