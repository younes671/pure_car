<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reservation;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

    #[Route('/user/profil/{idClient}', name: 'profil_user')]
    public function profilUser(User $idClient, ReservationRepository $reservationRepository): Response
    {
        $userProfil = $reservationRepository->find($idClient);
        return $this->render('user/profil .html.twig', [
            'usersProfil' => $userProfil,
        ]);
    }

}
