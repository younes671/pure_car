<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reservation;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

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


}
