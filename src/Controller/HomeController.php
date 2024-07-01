<?php

namespace App\Controller;

use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // affiche une image par catégorie pour le slider
    #[Route('/home', name: 'app_home')]
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
        return $this->render('home/index.html.twig', [
            'imagesParCategorie' => $imagesParCategorie,
            'categorie' => $categories
        ]);
    }
}
