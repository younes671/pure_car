<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 70)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Vehicule>
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'categorie')]
    private Collection $VehiculesCategorie;

    public function __construct()
    {
        $this->VehiculesCategorie = new ArrayCollection();
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
    public function getVehiculesCategorie(): Collection
    {
        return $this->VehiculesCategorie;
    }

    public function addVehiculesCategorie(Vehicule $vehiculesCategorie): static
    {
        if (!$this->VehiculesCategorie->contains($vehiculesCategorie)) {
            $this->VehiculesCategorie->add($vehiculesCategorie);
            $vehiculesCategorie->setCategorie($this);
        }

        return $this;
    }

    public function removeVehiculesCategorie(Vehicule $vehiculesCategorie): static
    {
        if ($this->VehiculesCategorie->removeElement($vehiculesCategorie)) {
            // set the owning side to null (unless already changed)
            if ($vehiculesCategorie->getCategorie() === $this) {
                $vehiculesCategorie->setCategorie(null);
            }
        }

        return $this;
    }
}
