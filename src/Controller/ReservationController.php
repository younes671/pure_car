<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Entity\Categorie;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\VehiculeRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        $vehicules = $vehiculeRepository->getAllVehiculesOrderedByMarque();
        return $this->render('reservation/index.html.twig', [
            'vehicules' => $vehicules
        ]);
    }

    #[Route('reservation/vehicules', name: 'app_vehicules')]
    public function vehicules(VehiculeRepository $vehiculeRepository): Response
    {
        $vehicules = $vehiculeRepository->getAllVehiculesOrderedByMarque();
            
        $serializedVehicules = [];
        foreach ($vehicules as $vehicule) {
            $serializedVehicules[] = [
                'id' => $vehicule->getId(),
                'marque' => $vehicule->getModele()->getMArque()->getNom(), 
                'modele' => $vehicule->getModele()->getNom(),
                'categorie' => $vehicule->getCategorie()->getNom(),
                'image' => $vehicule->getImg(),
                'prix' => $vehicule->getPrix()
            ];
        }
    
        return new JsonResponse(['vehicules' => $serializedVehicules], 200, ["Content-Type" => "application/json"]);
        
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

    #[Route('/reservation/reservationClient/{vehiculeId}', name: 'reservationClient_reservation')]
    public function reservationClient(Request $request, EntityManagerInterface $entityManager, VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
        $vehicule = $vehiculeRepository->findBy(['id' => $vehiculeId]);

        $reservationForm = $this->createForm(ReservationType::class);

        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            $reservation = $reservationForm->getData();
            $entityManager->persist($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation effectuée avec succès.');
            return $this->redirectToRoute('profil_user');
        }else{
            $this->addFlash('danger', 'La réservation a échouée.');
            return $this->redirectToRoute('profil_user');
        }

        return $this->render('reservation/detail_car.html.twig', [
            'vehicules' => $vehicule,
            'reservationForm' => $reservationForm
        ]);
    }

    #[Route('/vehicules/search', name: 'app_vehicules_search')]
    public function search(Request $request, VehiculeRepository $vehiculeRepository, CategorieRepository $categorieRepository, MarqueRepository $marqueRepository): JsonResponse
    {
        // Convertir le contenu JSON en tableau associatif
        $jsonData = json_decode($request->getContent(), true);

        // Récupérer les critères de recherche depuis les données JSON
        $category = $jsonData['category'] ?? null;
        $mark = $jsonData['mark'] ?? null;
        $place = $jsonData['nbPlace'] ?? null;
        $autonomyRange = $jsonData['autonomyRange'] ?? null;

        if ($autonomyRange) {
            // Définir les bornes de la fourchette d'autonomie
            $autonomyBounds = explode('-', $autonomyRange);
            $minAutonomy = $autonomyBounds[0];
            $maxAutonomy = $autonomyBounds[1];
        } else {
            // Si la fourchette d'autonomie est vide, définissez les bornes à null
            $minAutonomy = null;
            $maxAutonomy = null;
        }
        
        if($category || $mark || $place || $minAutonomy || $maxAutonomy){

             // Utiliser les critères pour filtrer les véhicules
            $filteredVehicules = $vehiculeRepository->searchByCriteria($category, $mark, $place, $minAutonomy, $maxAutonomy);
            $serializedVehicules = [];
            foreach ($filteredVehicules as $vehicule) {
                $serializedVehicules[] = [
                    'id' => $vehicule->getId(),
                    'marque' => $vehicule->getModele()->getMarque()->getNom(),
                    'modele' => $vehicule->getModele()->getNom(),
                    'image' => $vehicule->getImg(),
                    'autonomie' => $vehicule->getAutonomie(),
                    'prix' => $vehicule->getPrix(),
                    'nbPlace' => $vehicule->getNbPlace(),
                    'nbBagage' => $vehicule->getNbBagage(),
                ];
            }
            return new JsonResponse(['vehicules' => $serializedVehicules], 200, ["Content-Type" => "application/json"]);
        }
        
       
    }

}
