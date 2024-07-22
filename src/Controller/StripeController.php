<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Stripe\Charge;
use Stripe\Stripe;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Facture;
use App\Entity\Reservation;
use App\Repository\FactureRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    
    
#[Route('/stripe/create-charge', name: 'app_stripe_charge', methods: ['POST'])]
public function createCharge(Request $request, ReservationRepository $reservationRepository, MailerInterface $mailer, Security $security): Response
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
           $pdfFilePath = $this->generateInvoicePdf($reservation->getFacture(), $reservation);

           // Envoi de l'email avec la facture
           $this->sendInvoiceEmail($reservation->getEmail(), $pdfFilePath, $mailer);

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
            throw $this->createNotFoundException('Reservation not found.');
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
        $output = $dompdf->output();

        // Chemin pour sauvegarder le fichier PDF dans le dossier var
        $pdfDirectory = $this->getParameter('kernel.project_dir') . '/var/factures/';
        if (!is_dir($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }
        $pdfFileName = 'invoice_' . $facture->getNumeroFacture() . '.pdf';
        $pdfFilePath = $pdfDirectory . $pdfFileName;

        // Sauvegarde du fichier PDF
        file_put_contents($pdfFilePath, $output);

        return $pdfFilePath;
    }

    private function sendInvoiceEmail(string $recipientEmail, string $pdfFilePath, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('your@example.com')
            ->to($recipientEmail)
            ->subject('Votre facture')
            ->text('Veuillez trouver votre facture en pièce jointe.')
            ->attachFromPath($pdfFilePath);
        $mailer->send($email);
    }

}
