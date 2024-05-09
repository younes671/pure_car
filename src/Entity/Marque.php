<?php

namespace App\Entity;

use App\Repository\MarqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarqueRepository::class)]
class Marque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Modele>
     */
    #[ORM\OneToMany(targetEntity: Modele::class, mappedBy: 'marque')]
    private Collection $ModeleMarque;

    public function __construct()
    {
        $this->ModeleMarque = new ArrayCollection();
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
     * @return Collection<int, Modele>
     */
    public function getModeleMarque(): Collection
    {
        return $this->ModeleMarque;
    }

    public function addModeleMarque(Modele $modeleMarque): static
    {
        if (!$this->ModeleMarque->contains($modeleMarque)) {
            $this->ModeleMarque->add($modeleMarque);
            $modeleMarque->setMarque($this);
        }

        return $this;
    }

    public function removeModeleMarque(Modele $modeleMarque): static
    {
        if ($this->ModeleMarque->removeElement($modeleMarque)) {
            // set the owning side to null (unless already changed)
            if ($modeleMarque->getMarque() === $this) {
                $modeleMarque->setMarque(null);
            }
        }

        return $this;
    }
}
