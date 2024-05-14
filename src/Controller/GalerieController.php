<?php

namespace App\Controller;

use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalerieController extends AbstractController
{
    #[Route('/galerie', name: 'app_galerie')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        $vehicule = $vehiculeRepository->findAll();
        return $this->render('galerie/index.html.twig', [
            'vehicules' => $vehicule
        ]);
    }
}
