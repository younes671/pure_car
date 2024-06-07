<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Entity\Reservation;
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
        $user = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $user,
        ]);
    }


    #[Route('/user/detail', name: 'detail_user')]
    public function detailUser(ReservationRepository $reservationRepository): Response
    {
        $userReservation = $reservationRepository->findAll();
        return $this->render('user/listClient.html.twig', [
            'usersReservation' => $userReservation,
        ]);
    }

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

    #[Route('/user/profil/{idClient}', name: 'profil_user')]
    public function profilUser(User $idClient, Security $security, ReservationRepository $reservationRepository): Response
    {
        if ($security->isGranted('ROLE_ADMIN') || $security->isGranted('ROLE_USER'))
        {
        $reservation = $reservationRepository->findBy(['user' => $idClient]);
        // var_dump($reservation); exit;
        return $this->render('user/profil.html.twig', [
            'reservations' => $reservation,
        ]);
        }else{

            $this->addFlash('danger', 'Vous n\'avez pas accès à cette page, veuillez vous connectez.');
            return $this->redirectToRoute('app_login');
        }
    }

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


}
