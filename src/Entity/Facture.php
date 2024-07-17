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


    #[ORM\Column(length: 60)]
    private ?string $numeroFacture = null;

    #[ORM\Column]
    private ?int $montant = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'facture')]
    private Collection $facturesReservations;

    public function __construct()
    {
        $this->facturesReservations = new ArrayCollection();
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

    public function getNumeroFacture(): ?string
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(string $numeroFacture): static
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getFacturesReservations(): Collection
    {
        return $this->facturesReservations;
    }

    public function addFacturesReservation(Reservation $facturesReservation): static
    {
        if (!$this->facturesReservations->contains($facturesReservation)) {
            $this->facturesReservations->add($facturesReservation);
            $facturesReservation->setFacture($this);
        }

        return $this;
    }

    public function removeFacturesReservation(Reservation $facturesReservation): static
    {
        if ($this->facturesReservations->removeElement($facturesReservation)) {
            // set the owning side to null (unless already changed)
            if ($facturesReservation->getFacture() === $this) {
                $facturesReservation->setFacture(null);
            }
        }

        return $this;
    }
}
