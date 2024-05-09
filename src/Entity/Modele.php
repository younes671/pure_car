<?php

namespace App\Entity;

use App\Repository\ModeleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModeleRepository::class)]
class Modele
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Vehicule>
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'modele')]
    private Collection $ModeleVehicule;

    #[ORM\ManyToOne(inversedBy: 'ModeleMarque')]
    private ?Marque $marque = null;

    public function __construct()
    {
        $this->ModeleVehicule = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Vehicule>
     */
    public function getModeleVehicule(): Collection
    {
        return $this->ModeleVehicule;
    }

    public function addModeleVehicule(Vehicule $modeleVehicule): static
    {
        if (!$this->ModeleVehicule->contains($modeleVehicule)) {
            $this->ModeleVehicule->add($modeleVehicule);
            $modeleVehicule->setModele($this);
        }

        return $this;
    }

    public function removeModeleVehicule(Vehicule $modeleVehicule): static
    {
        if ($this->ModeleVehicule->removeElement($modeleVehicule)) {
            // set the owning side to null (unless already changed)
            if ($modeleVehicule->getModele() === $this) {
                $modeleVehicule->setModele(null);
            }
        }

        return $this;
    }

    public function getMarque(): ?Marque
    {
        return $this->marque;
    }

    public function setMarque(?Marque $marque): static
    {
        $this->marque = $marque;

        return $this;
    }
}
