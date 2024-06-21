<?php

namespace App\Controller;

use App\Entity\Marque;
use App\Entity\Vehicule;
use App\Form\MarqueFormType;
use App\Form\ModeleFormType;
use App\Form\VehiculeFormType;
use App\Form\CategorieFormType;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\VehiculeRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class EntrepriseController extends AbstractController
{
     // Page d'accueil de l'entreprise
     #[Route('/entreprise', name: 'app_entreprise')]
     public function index(): Response
     {
         return $this->render('entreprise/index.html.twig', [
             'controller_name' => 'EntrepriseController',
         ]);
     }
 
     // Gestion des différentes entités (marque, modèle, catégorie, détails du véhicule)
     #[Route('/gestionMultiple', name: 'app_gestionMultiple')]
     public function new(Request $request, EntityManagerInterface $entityManager, VehiculeRepository $vehiculeRepository, MarqueRepository $marqueRepository, ModeleRepository $modeleRepository, CategorieRepository $categorieRepository): Response
     {
         // Récupération des marques, modèles, catégories et véhicules existants
         $marque = $marqueRepository->findBy([], ['nom' => 'ASC']);
         $modele = $modeleRepository->findBy([], ['nom' => 'ASC']);
         $categorie = $categorieRepository->findBy([], ['nom' => 'ASC']);
         $vehicule = $vehiculeRepository->findAll();
 
         // Création des formulaires pour ajouter une marque, un modèle, une catégorie et les détails du véhicule
         $marqueForm = $this->createForm(MarqueFormType::class);
         $modeleForm = $this->createForm(ModeleFormType::class, null, ['marques' => $marque]);
         $categorieForm = $this->createForm(CategorieFormType::class);
         $detailVehicule = $this->createForm(VehiculeFormType::class, null, ['categories' => $categorie, 'modeles' => $modele]);
 
         // Traitement des soumissions de formulaire
         $marqueForm->handleRequest($request);
         $modeleForm->handleRequest($request);
         $categorieForm->handleRequest($request);
         $detailVehicule->handleRequest($request);
        
         // Ajout d'une nouvelle marque
         if ($marqueForm->isSubmitted() && $marqueForm->isValid()) {
             $marque = $marqueForm->getData();
             $entityManager->persist($marque);
             $entityManager->flush();
             $this->addFlash('success', 'Marque ajoutée avec succès.');
             return $this->redirectToRoute('app_gestionMultiple');
         }
 
         // Ajout d'un nouveau modèle
         if ($modeleForm->isSubmitted() && $modeleForm->isValid()) {
             $modele = $modeleForm->getData();
             $entityManager->persist($modele);
             $entityManager->flush();
             $this->addFlash('success', 'Modèle ajouté avec succès.');
             return $this->redirectToRoute('app_gestionMultiple');
         }
 
         // Ajout d'une nouvelle catégorie
         if ($categorieForm->isSubmitted() && $categorieForm->isValid()) {
             $categorie = $categorieForm->getData();
             $entityManager->persist($categorie);
             $entityManager->flush();
             $this->addFlash('success', 'Catégorie ajoutée avec succès.');
             return $this->redirectToRoute('app_gestionMultiple');
         }
 
         // Ajout des détails d'un nouveau véhicule
         if ($detailVehicule->isSubmitted() && $detailVehicule->isValid()) {
         }
 
         // Rendu de la vue avec les formulaires et les données existantes
         return $this->render('entreprise/gestionMultiple.html.twig', [
             'marqueForm' => $marqueForm->createView(),
             'modeleForm' => $modeleForm->createView(),
             'categorieForm' => $categorieForm->createView(),
             'detailVehiculeForm' => $detailVehicule->createView(),
             'marques' => $marque,
             'modeles' => $modele, 
             'categories' => $categorie,
             'vehicules' => $vehicule 
         ]);
     }
 

    #[Route('/entreprise/image/{id}', name: 'image_entreprise')]
    public function image(Vehicule $id, VehiculeRepository $vehiculeRepository): Response
    {
       $vehicule = $vehiculeRepository->find($id);

        return $this->render('entreprise/image.html.twig', [
            'vehicule' => $vehicule
        ]);
    }

    #[Route('/edit/{type}/{id}', name: 'edit_entity')]
    public function edit_entity($type, $id, Request $request, EntityManagerInterface $entityManager, Security $security, VehiculeRepository $vehiculeRepository, MarqueRepository $marqueRepository, ModeleRepository $modeleRepository, CategorieRepository $categorieRepository): Response
    {
        
        // Récupération des modèles et catégories existants
        $modele = $modeleRepository->findBy([], ['nom' => 'ASC']);
        $categorie = $categorieRepository->findBy([], ['nom' => 'ASC']);
        // Récupérer l'entité à éditer en fonction du type et de l'ID
        switch ($type) {
            case 'marque':
                $entity = $marqueRepository->find($id);
                $form = $this->createForm(MarqueFormType::class, $entity);
                break;
            case 'modele':
                $entity = $modeleRepository->find($id);
                $form = $this->createForm(ModeleFormType::class, $entity);
                break;
            case 'categorie':
                $entity = $categorieRepository->find($id);
                $form = $this->createForm(CategorieFormType::class, $entity);
                break;
            case 'detailVehicule':
                $entity = $vehiculeRepository->find($id);
                $form = $this->createForm(VehiculeFormType::class, $entity,  ['categories' => $categorie, 'modeles' => $modele]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // téléchargement nouvelle image
                    $uploadedFile = $form['img']->getData();
                    if ($uploadedFile) {
                        $newFileName = uniqid().'.'.$uploadedFile->guessExtension();
                        try {
                            $uploadedFile->move(
                                $this->getParameter('kernel.project_dir') . '/public/img/car',
                                $newFileName
                            );
                            $entity->setImg('/img/car/'.$newFileName);
                        } catch (FileException $e) {
                            // Gérer l'erreur de téléchargement du fichier
                            $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                            return $this->redirectToRoute('edit_entity', ['type' => $type, 'id' => $id]);
                        }
                    }
                    $entityManager->flush();
                    $this->addFlash('success', ucfirst($type) . ' modifié avec succès.');
                    return $this->redirectToRoute('app_gestionMultiple');
                }
                break;
            default:
                throw new \Exception('Type d\'entité non valide');
        }
    
        // Gestion de la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', ucfirst($type) . ' modifié avec succès.');
            return $this->redirectToRoute('app_gestionMultiple');
        }
    
        // Rendu de la vue avec le formulaire pour éditer l'entité
        return $this->render('entreprise/edit_entity.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]); 
   }

   #[Route('/delete/{type}/{id}', name: 'delete_entity')]
    public function delete_entity($type, $id, EntityManagerInterface $entityManager, Security $security, VehiculeRepository $vehiculeRepository, MarqueRepository $marqueRepository, ModeleRepository $modeleRepository, CategorieRepository $categorieRepository): Response
    {
            // Récupérer l'entité à supprimer en fonction du type et de l'ID
            switch ($type) {
                case 'marque':
                        $entity = $marqueRepository->find($id);
                    break;
                case 'modele':
                    $entity = $modeleRepository->find($id);
                    break;
                case 'categorie':
                    $entity = $categorieRepository->find($id);
                    break;
                case 'detailVehicule':
                    $entity = $vehiculeRepository->find($id);
                    break;
                default:
                    throw new \Exception('Type d\'entité non valide');
                }
    
                    // Vérifier si l'entité existe
                    if (!$entity) {
                        throw $this->createNotFoundException('Entité non trouvée.');
                    }
    
                    // Supprimer l'entité
                    $entityManager->remove($entity);
                    $entityManager->flush();
    
                    $this->addFlash('success', ucfirst($type) . ' supprimé avec succès.');
    
                    return $this->redirectToRoute('app_gestionMultiple');
    }

    // retourne mentions légales
    #[Route('/entreprise/mention', name: 'mention_entreprise')]
    public function mention(): Response
    {
        return $this->render('entreprise/mention.html.twig', [
            
        ]);
    }


    #[Route('/entreprise/emailContact', name: 'emailContact_entreprise')]
    public function emailContact( MailerInterface $mailer, Request $request): RedirectResponse
    {

        // Récupérer les données soumises par le formulaire
        $name = filter_var($request->request->get('name'), FILTER_SANITIZE_SPECIAL_CHARS);
        $firstname = filter_var($request->request->get('firstname'), FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($request->request->get('email'), FILTER_SANITIZE_EMAIL);
        $phone = filter_var($request->request->get('phone'), FILTER_SANITIZE_SPECIAL_CHARS);
        $subject = filter_var($request->request->get('subject'), FILTER_SANITIZE_SPECIAL_CHARS);
        $message = filter_var($request->request->get('message'), FILTER_SANITIZE_SPECIAL_CHARS);

        // Composer le contenu de l'email
        $emailContent = "Nom: $name\n";
        $emailContent .= "Prénom: $firstname\n";
        $emailContent .= "Email: $email\n";
        $emailContent .= "Téléphone: $phone\n\n";
        $emailContent .= "Message:\n$message";

        // Envoyer l'email
        $email = (new TemplatedEmail())
            ->from('user@email.com')
            ->to('admin@email.com') // Adresse email de réception
            ->subject($subject)
            ->text($emailContent);

        $mailer->send($email);

        
            $this->addFlash('success', 'Email envoyé avec succès.');
            return $this->redirectToRoute('app_entreprise');
        
    }

}
