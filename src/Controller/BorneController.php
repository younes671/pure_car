<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BorneController extends AbstractController
{
    #[Route('/borne', name: 'borne_list')]
    public function index(): Response
    {
        return $this->render('borne/index.html.twig');
    }

    
}