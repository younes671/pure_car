<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Facture;
use App\Entity\Vehicule;
use App\Entity\Categorie;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\FactureRepository;
use App\Repository\VehiculeRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    // récupère les vehicules par marque
    #[Route('/reservation', name: 'app_reservation')]
    public function index(VehiculeRepository $vehiculeRepository): Response
    {
        // récupère liste véhicule par marque
        $vehicules = $vehiculeRepository->getAllVehiculesOrderedByMarque();
        return $this->render('reservation/index.html.twig', [
            'vehicules' => $vehicules
        ]);
    }

    // récupère liste véhicule et convertit en Json pour manipulation DOM
    #[Route('reservation/vehicules', name: 'app_vehicules')]
    public function vehicules(VehiculeRepository $vehiculeRepository): Response
    {
        // récupère tous les vehicules triés par marque
        $vehicules = $vehiculeRepository->getAllVehiculesOrderedByMarque();
        // initialisation tableau
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

    // récupère détail d'un véhicule par son id
    #[Route('/reservation/detailCar/{vehiculeId}', name: 'detailCar_reservation')]
    public function detailCar(VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
        //récupère véhicule par son Id
        $vehicule = $vehiculeRepository->findBy(['id' => $vehiculeId]);
        return $this->render('reservation/detail_car.html.twig', [
            'vehicules' => $vehicule,
        ]);
    }

    // réservation avec compte utilisateur
    #[Route('/reservation/reservationClient/{vehiculeId}', name: 'reservationClient_reservation')]
    public function reservationClient(UserRepository $userRepository, Request $request, ReservationRepository $reservationRepository, FactureRepository $factureRepository, EntityManagerInterface $entityManager, VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        $userId = $userRepository->findOneBy(['id' => $user]);
        $vehicule = $vehiculeRepository->findOneBy(['id' => $vehiculeId]);


        // Si l'utilisateur n'est pas connecté
        if (!$user) {
            // Redirection
            return $this->redirectToRoute('reservation_choice', ['vehiculeId' => $vehiculeId->getId()]);
        }

        // Créer une nouvelle instance de réservation
        $reservation = new Reservation();

        if ($user->getUserReservation()->count() > 0) {
        $latestReservation = $user->getUserReservation()->last();
        $reservation->setEmail($latestReservation->getEmail());
        $reservation->setNom($latestReservation->getNom());
        $reservation->setPrenom($latestReservation->getPrenom());
        $reservation->setAdresse($latestReservation->getAdresse());
        $reservation->setCp($latestReservation->getCp());
        $reservation->setVille($latestReservation->getVille());
    } else {
        $reservation->setEmail($user->getEmail());
    }

        // Création du formulaire de réservation
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

            // Vérification des dates de réservation
            $currentDate = new \DateTime(); // Date actuelle

            // Vérifier si la date de début est antérieure à la date actuelle
            if ($dateDebut < $currentDate) {
                $this->addFlash('error', 'La date de début ne peut pas être antérieure à la date actuelle.');
                return $this->render('reservation/reservation.html.twig', [
                    'vehicule' => $vehicule,
                    'reservationForm' => $reservationForm->createView()
                ]);
            }
            // Vérifier si la date de fin est antérieure à la date de début
            if ($dateFin < $dateDebut) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');
                return $this->render('reservation/reservation.html.twig', [
                    'vehicule' => $vehicule,
                    'reservationForm' => $reservationForm->createView()
                ]);
            }
            $nbJours = $dateDebut->diff($dateFin)->days;
            if($nbJours === 0){
                $nbJours = 1;
            }

            // Vérifier si le véhicule est déjà réservé
            if ($reservationRepository->isVehiculeReserved($vehicule, $dateDebut, $dateFin)) {
                $this->addFlash('error', 'Ce véhicule est déjà réservé pour la période sélectionnée.');
                return $this->render('reservation/reservation.html.twig', [
                    'vehicule' => $vehicule,
                    'reservationForm' => $reservationForm->createView()
                ]);
            }

            // Générer un numéro de réservation unique
            $lastReservation = $reservationRepository->findOneBy([], ['id' => 'desc']);
             $lastReservationNumber = $lastReservation ? $lastReservation->getNumeroReservation() : 0;
             $newReservationNumber = sprintf('%06d', (int) $lastReservationNumber + 1);
             $prixTotal = $nbJours * $vehicule->getPrix();

            $reservation->setNumeroReservation($newReservationNumber);

             // Génération du numéro de facture unique
             $lastInvoice = $factureRepository->findOneBy([], ['id' => 'desc']);
             $lastInvoiceNumber = $lastInvoice ? $lastInvoice->getNumeroFacture() : 0;
             $newInvoiceNumber = sprintf('%06d', $lastInvoiceNumber + 1);
             $prixTotal = $nbJours * $vehicule->getPrix();
 
             // Création de l'entité facture
             $facture = new Facture();
             $facture->setNumeroFacture($newInvoiceNumber);
             $facture->setMontant($prixTotal); // Ajustez le montant selon vos besoins
             $facture->setDateCreation(new \DateTime());
             $facture->setDateEmission(new \DateTime());
             // $reservation->setUser($reservation->getUser());
             
             // Associe la facture à la réservation
             
             $entityManager->persist($facture);
             $entityManager->flush();
            
            $reservation->setPrix($prixTotal);
            $reservation->setFacture($facture);
            $entityManager->persist($reservation);// prépare requete
            $entityManager->flush();// enregistrement dans bdd

              // Mettre à jour les informations utilisateur s'il s'agit de la première réservation
              if ($user->getNom() === null || $user->getNom() === '') {
                $user->setNom($reservation->getNom());
                $user->setPrenom($reservation->getPrenom());
                $user->setAdresse($reservation->getAdresse());
                $user->setCp($reservation->getCp());
                $user->setVille($reservation->getVille());

                // Sauvegarder les modifications de l'utilisateur
                $entityManager->persist($user);
                $entityManager->flush();
            }

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

    // réservation sans compte utilisateur
    #[Route('/reservation/reservationInvite/{vehiculeId}', name: 'reservationInvite_reservation')]
    public function reservationInvite(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, FactureRepository $factureRepository, VehiculeRepository $vehiculeRepository, Vehicule $vehiculeId): Response
    {
         
        $vehicule = $vehiculeRepository->findOneBy(['id' => $vehiculeId]);

        $reservationForm = $this->createForm(ReservationType::class);

        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            $reservation = $reservationForm->getData();
            $vehicule = $vehiculeRepository->find($vehiculeId);
            $reservation->setVehicule($vehicule);

            // Calcul du prix en fonction de la durée de location
            $dateDebut = $reservation->getDateDebut();
            $dateFin = $reservation->getDateFin();

            // Vérification des dates de réservation
            $currentDate = new \DateTime(); // Date actuelle

            // Vérifier si la date de début est antérieure à la date actuelle
            if ($dateDebut < $currentDate) {
                $this->addFlash('error', 'La date de début ne peut pas être antérieure à la date actuelle.');
                return $this->render('reservation/reservation.html.twig', [
                    'vehicule' => $vehicule,
                    'reservationForm' => $reservationForm->createView()
                ]);
            }
            // Vérifier si la date de fin est antérieure à la date de début
            if ($dateFin < $dateDebut) {
                $this->addFlash('error', 'La date de fin doit être postérieure à la date de début.');
                return $this->render('reservation/reservation.html.twig', [
                    'vehicule' => $vehicule,
                    'reservationForm' => $reservationForm->createView()
                ]);
            }
            $nbJours = $dateDebut->diff($dateFin)->days;
            if($nbJours === 0){
                $nbJours = 1;
            }

            // Vérifier si le véhicule est déjà réservé
            if ($reservationRepository->isVehiculeReserved($vehicule, $dateDebut, $dateFin)) {
                $this->addFlash('error', 'Ce véhicule est déjà réservé pour la période sélectionnée.');
                return $this->render('reservation/reservation.html.twig', [
                    'vehicule' => $vehicule,
                    'reservationForm' => $reservationForm->createView()
                ]);
            }

            // Générer un numéro de réservation unique
            $lastReservation = $reservationRepository->findOneBy([], ['id' => 'desc']);
             $lastReservationNumber = $lastReservation ? $lastReservation->getNumeroReservation() : 0;
             $newReservationNumber = sprintf('%06d', (int) $lastReservationNumber + 1);
             $prixTotal = $nbJours * $vehicule->getPrix();

            $reservation->setNumeroReservation($newReservationNumber);

            // Génération du numéro de facture unique
            $lastInvoice = $factureRepository->findOneBy([], ['id' => 'desc']);
            $lastInvoiceNumber = $lastInvoice ? $lastInvoice->getNumeroFacture() : 0;
            $newInvoiceNumber = sprintf('%06d', $lastInvoiceNumber + 1);
            $prixTotal = $nbJours * $vehicule->getPrix();

            // Création de l'entité facture
            $facture = new Facture();
            $facture->setNumeroFacture($newInvoiceNumber);
            $facture->setMontant($prixTotal); // Ajustez le montant selon vos besoins
            $facture->setDateCreation(new \DateTime());
            $facture->setDateEmission(new \DateTime());
            // $reservation->setUser($reservation->getUser());
            
            // Associe la facture à la réservation
            
            $entityManager->persist($facture);
            $entityManager->flush();

            $reservation->setPrix($prixTotal);
            $reservation->setFacture($facture);
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
                'reservationForm' => $reservationForm

        ]);
    }

    // choix du véhicule
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

   

    //annuler réservation
    #[Route('/reservation/annuler/{reservationId}', name: 'annuler_reservation')]
    public function annulerReservation($reservationId, UserRepository $userRepository, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): RedirectResponse
    {
        
        $reservation = $reservationRepository->find($reservationId);
        $user = $this->getUser();
        $userId = $userRepository->findOneBy(['id' => $user]);
          // Vérifier si la réservation existe
        if (!$reservation) {
            throw $this->createNotFoundException('La réservation n\'existe pas.');
        }
         // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            // Rediriger vers la page de choix si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('reservation_choice', ['vehiculeId' => $reservation->getVehicule()->getId()]);
        }

        // Vérifier si l'e-mail est présent
        if (!$reservation->getEmail()) {
            // Rediriger vers la page de choix si la réservation ou l'e-mail est absent
            return $this->redirectToRoute('reservation_choice', ['vehiculeId' => $reservation->getVehicule()->getId()]);
        }

        // Supprimer la réservation de la base de données
        $entityManager->remove($reservation);
        $entityManager->flush();

        if ($user) {
            $this->addFlash('success', 'La réservation a été annulée avec succès.');
            return $this->redirectToRoute('profil_user', ['idClient' => $userId->getId()]);
        } else {
            $this->addFlash('success', 'La réservation a été annulée avec succès.');
            return $this->redirectToRoute('app_home');
        }
    }

   


    // recherche véhicule selon critère choisit
    #[Route('/vehicules/search', name: 'app_vehicules_search')]
    public function search(Request $request, VehiculeRepository $vehiculeRepository): JsonResponse
    {
        // Convertir le contenu JSON en tableau associatif
        $jsonData = json_decode($request->getContent(), true);

        // Récupère les critères de recherche depuis les données JSON
        $category = $jsonData['category'] ?? null;
        $mark = $jsonData['mark'] ?? null;
        $place = $jsonData['nbPlace'] ?? null;
        $autonomyRange = $jsonData['autonomyRange'] ?? null;

        if ($autonomyRange) {
            // Définit les bornes de la fourchette d'autonomie
            $autonomyBounds = explode('-', $autonomyRange);
            $minAutonomy = $autonomyBounds[0];
            $maxAutonomy = $autonomyBounds[1];
        } else {
            // Si la fourchette d'autonomie est vide
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
