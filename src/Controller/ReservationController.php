<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vehicule;
use App\Entity\Categorie;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\UserRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\VehiculeRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function reservationClient(UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        $userId = $userRepository->findOneBy(['id' => $user]);
        $vehicule = $vehiculeRepository->findOneBy(['id' => $vehiculeId]);


        // Si l'utilisateur n'est pas connecté
        if (!$user) {
            // Rediriger vers la page de connexion
            return $this->redirectToRoute('reservation_choice', ['vehiculeId' => $vehiculeId->getId()]);
        }

        // Créer une nouvelle instance de réservation
        $reservation = new Reservation();

        // Remplir automatiquement les autres champs avec les informations de l'utilisateur
        $reservation->setNom($userId->getNom());
        $reservation->setPrenom($userId->getPrenom());
        $reservation->setAdresse($userId->getAdresse());
        $reservation->setCp($userId->getCp());
        $reservation->setVille($userId->getVille());

        // Créer le formulaire de réservation
        $reservationForm = $this->createForm(ReservationType::class, $reservation);

        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            $reservation = $reservationForm->getData();
            $vehicule = $vehiculeRepository->find($vehiculeId);
            $reservation->setVehicule($vehicule);
            $reservation->setUser($user); // Enregistrer l'utilisateur avec la réservation

            // Calcul du prix en fonction de la durée de location
            $dateDebut = $reservation->getDateDebut();
            $dateFin = $reservation->getDateFin();
            $nbJours = $dateDebut->diff($dateFin)->days;
            $prixTotal = $nbJours * $vehicule->getPrix();
            $reservation->setPrix($prixTotal);

            $entityManager->persist($reservation);
            $entityManager->flush();

            // Afficher les détails de la réservation et les options de confirmation ou d'annulation
            return $this->render('reservation/confirmation.html.twig', [
                'reservation' => $reservation,
                'vehicule' => $vehicule,
            ]);
            }

        return $this->render('reservation/reservation.html.twig', [
            'vehicule' => $vehicule,
            'reservationForm' => $reservationForm->createView()
        ]);
    }


    #[Route('/reservation/reservationInvite/{vehiculeId}', name: 'reservationInvite_reservation')]
    public function reservationInvite(Request $request, EntityManagerInterface $entityManager, VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
         
        $vehicule = $vehiculeRepository->findOneBy(['id' => $vehiculeId]);

        $reservationForm = $this->createForm(ReservationType::class);

        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            $reservation = $reservationForm->getData();
            $vehicule = $vehiculeRepository->find($vehiculeId);
            $reservation->setVehicule($vehicule);
            $entityManager->persist($reservation);
            $entityManager->flush();
                // Vérification de la réussite de la réservation
                if ($reservation->getId()) {
                    $this->addFlash('success', 'Réservation effectuée avec succès.');
                    return $this->redirectToRoute('app_home');
                } else {
                    // Si la réservation échoue, ajoutez un message d'erreur
                    $this->addFlash('error', 'La réservation a échoué. Veuillez réessayer.');
                }
        }
            return $this->render('reservation/reservation.html.twig', [
                'vehicule' => $vehicule,
                'reservationForm' => $reservationForm

        ]);
    }

    #[Route('/reservation/choice/{vehiculeId}', name: 'reservation_choice')]
    public function reservationChoice($vehiculeId, VehiculeRepository $vehiculeRepository, Request $request): Response
    {
        $vehicule = $vehiculeRepository->find($vehiculeId);
        // Stocker l'ID du véhicule dans la session
        $request->getSession()->set('selected_vehicle_id', $vehiculeId);

        return $this->render('reservation/choice.html.twig', [
            'vehicule' => $vehicule
        ]);
    }

    #[Route('/reservation/confirmation/{reservationId}', name: 'reservation_confirmation')]
    public function confirmationReservation($reservationId, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérère les détails de la réservation à partir de l'ID
        $reservation = $reservationRepository->find($reservationId);
        // Affiche la page de confirmation avec les détails de la réservation
        return $this->render('reservation/confirmation.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/annuler/{reservationId}', name: 'reservation_annuler', methods: ['POST'])]
    public function annulerReservation($reservationId, UserRepository $userRepository, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): RedirectResponse
    {
        $reservation = $reservationRepository->find($reservationId);
        $user = $this->getUser();
        $userId = $userRepository->findOneBy(['id' => $user]);
        // Supprimer la réservation de la base de données
        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a été annulée avec succès.');
        return $this->redirectToRoute('profil_user', ['idClient' => $userId->getId()]);
    }

    #[Route('/reservation/confirmer/{reservationId}', name: 'reservation_confirmer', methods: ['POST'])]
    public function confirmerReservation($reservationId, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, UserRepository $userRepository): RedirectResponse
    {
        $user = $this->getUser();
        $userId = $userRepository->findOneBy(['id' => $user]);

        $reservation = $reservationRepository->find($reservationId);
        $reservation->setConfirmation(true);
        $entityManager->flush();
        $this->addFlash('success', 'La réservation a été confirmée avec succès.');
        return $this->redirectToRoute('profil_user', ['idClient' => $userId->getId()]);
    }


    #[Route('/vehicules/search', name: 'app_vehicules_search')]
    public function search(Request $request, VehiculeRepository $vehiculeRepository): JsonResponse
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
