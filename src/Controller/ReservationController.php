<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }

    #[Route('/reservation/detailCar', name: 'detailCar_reservation')]
    public function detailCar(): Response
    {
        return $this->render('reservation/detail_car.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }
}
