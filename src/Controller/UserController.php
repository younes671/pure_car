<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Entity\Reservation;
use App\Repository\FactureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        // récupère liste de tous les utilisateurs
        $user = $userRepository->findBy(['archived' => false], ["id" => "ASC"]);
        return $this->render('user/index.html.twig', [
            'users' => $user,
        ]);
    }

    #[Route('/user/Archived', name: 'app_userArchived')]
    public function clientArchive(UserRepository $userRepository, Security $security): Response
    {
        
            $userArchive = $userRepository->findBy(['archived' => true], ["id" => "ASC"]);
            return $this->render('user/userArchived.html.twig', [
                'usersArchived' => $userArchive
            ]);
       
    }


    #[Route('/user/detail', name: 'detail_user')]
    public function detailUser(ReservationRepository $reservationRepository): Response
    {
        // récupère liste de toutes les réservations de tous les utilisateurs
        $userReservation = $reservationRepository->findAll();
        return $this->render('user/listClient.html.twig', [
            'usersReservation' => $userReservation,
        ]);
    }

    //suppression utilisateur
    #[Route('/user/delete/{id}', name: 'delete_user')]
    public function supprimerUtilisateur(User $user, UserRepository $userRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        
        // supprimer toutes les réservations de l'utilisateur
        foreach ($user->getUserReservation() as $reservation) {
            $entityManager->remove($reservation);
        }
        //supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès');
        return $this->redirectToRoute('app_register');
    }

    // affiche profil utilisateur
    #[Route('/user/profil/{idClient}', name: 'profil_user')]
    public function profilUser(User $idClient, Security $security, UserRepository $userRepository,  ReservationRepository $reservationRepository): Response
    {
        
        $reservations = $reservationRepository->findBy(['user' => $idClient]);
        $user = $userRepository->find($idClient);
        // Collecte des factures associées aux réservations
        $factures = [];
        foreach ($reservations as $reservation) {
            $facture = $reservation->getFacture();
            if ($facture) {
                $factures[] = $facture;
            }
        }

        return $this->render('user/profil.html.twig', [
            'reservations' => $reservations,
            'factures' => $factures,
            'user' => $user
        ]);
        
    }

    // edit le profil utilisateur
    #[Route('/user/edit/{id}', name: 'edit_user')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un formulaire pour l'édition de l'utilisateur
        $editForm = $this->createForm(UserFormType::class, $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // Enregistrer les modifications dans la base de données
            $entityManager->flush();

            $this->addFlash('success', 'Informations modifiées avec succès.');
            return $this->redirectToRoute('profil_user', ['idClient' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'editForm' => $editForm->createView(),
        ]);
    }


    #[Route('/user/archiver/{id}', name: 'archiver_user')]
    public function archiver(User $user, EntityManagerInterface $entityManager, Security $security): Response
    {
       
            $user->setArchived(true);
            $user->setPseudo("anonyme");
            $user->setEmail(md5("anonyme"));
            $user->setAdresse("anonyme");
            $user->setCp("anonyme");
            $user->setVille("anonyme");
            $user->setNom("anonyme");
            $user->setPrenom("anonyme");
            $entityManager->flush();
            $this->addFlash('success', 'Vos données ont été supprimées');

            return $this->redirectToRoute('app_home');
        
    }

    #[Route('/desArchiver/{id}', name: 'desArchiver_user')]
    public function desArchiver(User $user, EntityManagerInterface $entityManager, Security $security): Response
    {
        
            $user->setArchived(false);
            $entityManager->flush();
            $this->addFlash('success', 'L\'utilisateur a été desarchivé avec succès.');

            return $this->redirectToRoute('app_userArchived');
        
    }

    #[Route('/user/archived/{id}', name: 'show_user_archived')]
        public function show_clientArchived(User $user, ReservationRepository $reservationRepository, Security $security): Response
        {
            
                // Vérifier si l'utilisateur est archivé
                if ($user->isArchived()) {
                    $reservations = $reservationRepository->findBy(['user' => $user]);
                    return $this->render('user/show.html.twig', [
                        'user' => $user,
                        'reservations' => $reservations
                    ]);
                } else {
                    // Gérer le cas où l'utilisateur n'est pas archivé, par exemple, rediriger vers une page d'erreur
                    throw $this->createNotFoundException('Cette utilisateur n\'est pas archivé.');
                }
            
        }

}
