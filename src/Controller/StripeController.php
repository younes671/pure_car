<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Stripe\Charge;
use Stripe\Stripe;
use App\Entity\PDF;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Facture;
use App\Entity\Reservation;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    
    
#[Route('/stripe/create-charge', name: 'app_stripe_charge', methods: ['POST'])]
public function createCharge(Request $request, ReservationRepository $reservationRepository, FactureRepository $factureRepository, EntityManagerInterface $entityManager, MailerInterface $mailer, Security $security): Response
{
    
    Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

    $user = $security->getUser();
    
    try {
        $reservationId = $request->request->get('reservationId');
        $reservation = $reservationRepository->find($reservationId);

        $charge = Charge::create([
            "amount" => $reservation->getPrix(),
            "currency" => "eur",
            "source" => $request->request->get('stripeToken'),
            "description" => "Payment for reservation #" . $reservation->getId(),
        ]);

        // Assurez-vous que le paiement a été effectué avec succès
        if ($charge->status == 'succeeded') {
            
           // Génération du PDF de la facture
           $pdfContent = $this->generateInvoicePdf($reservation->getFacture(), $reservation);

           // Sauvegarde du PDF dans la base de données
           $pdf = new PDF();
           $pdf->setLibelle($pdfContent);

           // Associer le PDF à la facture
           $facture = $reservation->getFacture();
           $facture->setFacturePDF($pdf);

           // Persist le PDF et la Facture
           $entityManager->persist($pdf);
           $entityManager->persist($facture);
           $entityManager->flush();

           // Envoi de l'email avec la facture
           $this->sendInvoiceEmail($reservation->getEmail(), $pdfContent, $mailer);

            $this->addFlash('success', 'Paiement réussie avec succès!');
        } else {
            $this->addFlash('error', 'Payment failed!');
        }
    } catch (\Exception $e) {
        $this->addFlash('error', 'An error occurred during the payment process:' . $e->getMessage());
    }

    if ($user) {
        return $this->redirectToRoute('profil_user', ['idClient' => $reservation->getUser()->getId()], Response::HTTP_SEE_OTHER);
    } else {
        return $this->redirectToRoute('app_home');
    }
    

    return $this->redirectToRoute('profil_user', ['idClient' => $reservation->getUser()->getId()], Response::HTTP_SEE_OTHER);
}

#[Route('/stripe/{reservationId}', name: 'app_stripe')]
    public function index($reservationId, ReservationRepository $reservationRepository): Response
    {
        // error_log('Reservation ID received: ' . $reservationId);
        // var_dump($reservationId); exit;
        $reservation = $reservationRepository->find($reservationId);
        // var_dump($reservation); exit;

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        return $this->render('stripe/index.html.twig', [
            'stripe_key' => $_ENV["STRIPE_KEY"],
            'reservation' => $reservation
        ]);
    }

    private function generateInvoicePdf(Facture $facture, Reservation $reservation): string
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('facture/index.html.twig', [
            'facture' => $facture,
            'reservation' => $reservation
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Retourne le contenu PDF sous forme de chaîne de caractères
        return $dompdf->output();
    }

    private function sendInvoiceEmail(string $recipientEmail, string $pdfContent, MailerInterface $mailer): void
    {
        // Chemin pour stocker temporairement le PDF
        $tempPdfPath = $this->getParameter('pdf_directory') . 'invoice_temp.pdf';
        
        // Écrire le contenu du PDF dans un fichier temporaire
        file_put_contents($tempPdfPath, $pdfContent);

        $email = (new Email())
            ->from('your@example.com')
            ->to($recipientEmail)
            ->subject('Votre facture')
            ->text('Veuillez trouver votre facture en pièce jointe.')
            ->attachFromPath($tempPdfPath);

            try {
                $mailer->send($email);
                // Supprimez le fichier temporaire après l'envoi
                unlink($tempPdfPath);
            } catch (\Exception $e) {
                // Gérer l'erreur
                $this->addFlash('error', 'L\'envoi de l\'email a échoué : ' . $e->getMessage());
            }
    }
}
