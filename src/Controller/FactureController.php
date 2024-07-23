<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture')]
    public function index(): Response
    {
        return $this->render('facture/index.html.twig', [
            'controller_name' => 'FactureController',
        ]);
    }

    #[Route('/show-pdf/{factureId}', name: 'show_pdf')]
    public function showPdfAction($factureId, FactureRepository $factureRepository, EntityManagerInterface $entityManager)
    {
        // Récupérer l'entité Facture
        $facture = $factureRepository->find($factureId);

        if (!$facture) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        // Récupérer l'entité PDF associée à la facture
        $pdf = $facture->getFacturePDF();

        if (!$pdf) {
            throw $this->createNotFoundException('PDF non trouvé pour cette facture');
        }
        // Lie le contenu le convertit en chaine de caractère et Récupérer le contenu du PDF
        $pdfContent = stream_get_contents($pdf->getLibelle());
        
        if (!is_string($pdfContent)) {
        // Gérer l'erreur, par exemple en renvoyant une réponse d'erreur
        return new Response('Erreur: Contenu PDF invalide', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Créer une réponse HTTP avec le contenu du PDF
        $response = new Response($pdfContent);

        // Ajouter les en-têtes pour indiquer que la réponse est un PDF
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="facture.pdf"');

        return $response;
    }
}
