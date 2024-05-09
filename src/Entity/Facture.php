<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEmission = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'facture')]
    private Collection $FacturesReservation;

    public function __construct()
    {
        $this->FacturesReservation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateEmission(): ?\DateTimeInterface
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTimeInterface $dateEmission): static
    {
        $this->dateEmission = $dateEmission;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getFacturesReservation(): Collection
    {
        return $this->FacturesReservation;
    }

    public function addFacturesReservation(Reservation $facturesReservation): static
    {
        if (!$this->FacturesReservation->contains($facturesReservation)) {
            $this->FacturesReservation->add($facturesReservation);
            $facturesReservation->setFacture($this);
        }

        return $this;
    }

    public function removeFacturesReservation(Reservation $facturesReservation): static
    {
        if ($this->FacturesReservation->removeElement($facturesReservation)) {
            // set the owning side to null (unless already changed)
            if ($facturesReservation->getFacture() === $this) {
                $facturesReservation->setFacture(null);
            }
        }

        return $this;
    }
}
