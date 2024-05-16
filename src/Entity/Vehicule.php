<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $autonomie = null;

    #[ORM\Column]
    private ?int $nbPorte = null;

    #[ORM\Column]
    private ?int $nbPlace = null;

    #[ORM\Column]
    private ?bool $bluetooth = null;

    #[ORM\Column]
    private ?bool $climatisation = null;

    #[ORM\Column]
    private ?bool $gps = null;

    #[ORM\Column]
    private ?int $nbBagage = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\ManyToOne(inversedBy: 'VehiculesCategorie')]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'ModeleVehicule')]
    private ?Modele $modele = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'vehicule')]
    private Collection $VehiculeReservations;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $img = null;

    public function __construct()
    {
        $this->VehiculeReservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAutonomie(): ?int
    {
        return $this->autonomie;
    }

    public function setAutonomie(int $autonomie): static
    {
        $this->autonomie = $autonomie;

        return $this;
    }

    public function getNbPorte(): ?int
    {
        return $this->nbPorte;
    }

    public function setNbPorte(int $nbPorte): static
    {
        $this->nbPorte = $nbPorte;

        return $this;
    }

    public function getNbPlace(): ?int
    {
        return $this->nbPlace;
    }

    public function setNbPlace(int $nbPlace): static
    {
        $this->nbPlace = $nbPlace;

        return $this;
    }

    public function isBluetooth(): ?bool
    {
        return $this->bluetooth;
    }

    public function setBluetooth(bool $bluetooth): static
    {
        $this->bluetooth = $bluetooth;

        return $this;
    }

    public function isClimatisation(): ?bool
    {
        return $this->climatisation;
    }

    public function setClimatisation(bool $climatisation): static
    {
        $this->climatisation = $climatisation;

        return $this;
    }

    public function isGps(): ?bool
    {
        return $this->gps;
    }

    public function setGps(bool $gps): static
    {
        $this->gps = $gps;

        return $this;
    }

    public function getNbBagage(): ?int
    {
        return $this->nbBagage;
    }

    public function setNbBagage(int $nbBagage): static
    {
        $this->nbBagage = $nbBagage;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getModele(): ?Modele
    {
        return $this->modele;
    }

    public function setModele(?Modele $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getVehiculeReservations(): Collection
    {
        return $this->VehiculeReservations;
    }

    public function addVehiculeReservation(Reservation $vehiculeReservation): static
    {
        if (!$this->VehiculeReservations->contains($vehiculeReservation)) {
            $this->VehiculeReservations->add($vehiculeReservation);
            $vehiculeReservation->setVehicule($this);
        }

        return $this;
    }

    public function removeVehiculeReservation(Reservation $vehiculeReservation): static
    {
        if ($this->VehiculeReservations->removeElement($vehiculeReservation)) {
            // set the owning side to null (unless already changed)
            if ($vehiculeReservation->getVehicule() === $this) {
                $vehiculeReservation->setVehicule(null);
            }
        }

        return $this;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function setImg($img): static
    {
        $this->img = $img;

        return $this;
    }
}
