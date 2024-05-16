<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\VehiculeRepository;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GalerieController extends AbstractController
{
    #[Route('/galerie', name: 'app_galerie')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
    // Récupérer toutes les catégories disponibles
    $categories = $vehiculeRepository->findAllCategories();

    // Initialiser un tableau pour stocker une seule image par catégorie
    $imagesParCategorie = [];

    // Pour chaque catégorie, récupérer une seule image
    foreach ($categories as $categorie) {
        // Récupérer une seule image pour cette catégorie
        $image = $vehiculeRepository->findImageByCategorie($categorie);

        // Ajouter l'image à la liste des images par catégorie
        if ($image) {
            $imagesParCategorie[$categorie->getNom()] = $image;
        }
    }

    return $this->render('galerie/index.html.twig', [
        'imagesParCategorie' => $imagesParCategorie
    ]);
    }


    #[Route('/galerie/vehicules/{categorie}', name: 'vehiculeByCategorie_galerie')]
    public function vehiculeByCategorie(VehiculeRepository $vehiculeRepository, Categorie $categorie): Response
    {
        // Récupérer tous les véhicules de la catégorie spécifiée
        $vehicules = $vehiculeRepository->findBy(['categorie' => $categorie]);
    
        return $this->render('galerie/listVehiculeByCategorie.html.twig', [
            'vehicules' => $vehicules,
            'categorie' => $categorie
        ]);
    }
    
}
