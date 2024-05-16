<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Repository\ModeleRepository;
use App\Repository\VehiculeRepository;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        $vehicules = $vehiculeRepository->findAll();
        return $this->render('reservation/index.html.twig', [
            'vehicules' => $vehicules
        ]);
    }

    #[Route('/vehicules', name: 'app_vehicules')]
    public function vehicules(CategorieRepository $vehiculeRepository): Response
    {
        $vehicules = $vehiculeRepository->findAll();
    

    return new JsonResponse(['vehicules' => $vehicules], 200, ["Content-Type" => "application/json"]);


        
        // $serializedVehicules = [];
        // foreach ($vehicules as $vehicule) {
        //     $serializedVehicules[] = [
        //         'id' => $vehicule->getId(),
        //         'marque' => $vehicule->getPrix(), // Assurez-vous d'ajuster ces propriétés en fonction de votre classe Vehicule
        //         'modele' => $vehicule->getModele()->getMarque()->getNom(),
        //         // Ajoutez d'autres propriétés selon vos besoins
        //     ];
        // }
    
        // return new JsonResponse(['vehicules' => $serializedVehicules], 200, ["Content-Type" => "application/json"]);
        
    }

    #[Route('/reservation/detailCar/{vehiculeId}', name: 'detailCar_reservation')]
    public function detailCar(VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
        $vehicule = $vehiculeRepository->findBy(['id' => $vehiculeId]);
        // var_dump($vehicule); exit;
        return $this->render('reservation/detail_car.html.twig', [
            'vehicules' => $vehicule,
        ]);
    }
}
