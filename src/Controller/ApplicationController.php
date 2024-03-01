<?php

namespace App\Controller;

use DateTime;
use App\Entity\Taches;
use App\Entity\Evenements;
use App\Form\TachesFormType;
use App\Services\UploadService;
use App\Form\EvenementsFormType;
use App\Repository\EvenementsRepository;
use App\Repository\TachesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApplicationController extends AbstractController
{

    public function __construct(
        private UploadService $uploadService,
    )
    {

    }
    
    #[Route('/application', name: 'app_application_index'), IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager, TachesRepository $tachesRepository, EvenementsRepository $evenementsRepository): Response
    {
        $evenement = new Evenements();
        $formEvents = $this->createForm(EvenementsFormType::class, $evenement);
        $formEvents->handleRequest($request);

        if ($formEvents->isSubmitted() && $formEvents->isValid()) {

            // Si on réceptionne une image d'illustration
            if ($formEvents->get('visuel')->getData()) {
                // On récupère l'image
                $fichier = $formEvents->get('visuel')->getData();
                // On récupère le répertoire de destination
                $directory = 'events_directory';
                // Puis on upload la nouvelle image et on ajoute cela à l'évènement
                $evenement->setVisuel('/images/evenements/' .$this->uploadService->send($fichier, $directory));
            }

            $date = $formEvents->get('dateEvents')->getData();
            $dateCreate = new DateTime($date);
            $evenement->setDateEvents($dateCreate)
                      ->setCreatedAt(new DateTime())
                      ->setAuteur($this->getUser());

            
            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('sucess', 'Votre évènement a bien été enregistré');

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
            
        }
        
        $tache = new Taches();

        $formTache = $this->createForm(TachesFormType::class, $tache);
        $formTache->handleRequest($request);

        $page = $request->query->getInt('page', 1);

        $evenements = $evenementsRepository->findEvenements($page, 5);

        if ($formTache->isSubmitted() && $formTache->isValid()) {

            $date = $formTache->get('delai')->getData();
            $dateCreate = new DateTime($date);

            $tache->setDelai($dateCreate);
            $tache->setStatut(1);
            $tache->setCreatedAt(new DateTime());
            $tache->setAuteur($this->getUser());

            $entityManager->persist($tache);
            $entityManager->flush();

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('application/index.html.twig', [
            'tache' => $tache,
            'formTache' => $formTache,
            'taches' => $tachesRepository->findBy(['statut' => true]),
            'formEvents' => $formEvents,
            'evenements' => $evenements
        ]);
    }
    #[Route('/application/tache-faite/{id}', name: 'app_application_validation'), IsGranted('ROLE_USER')]
    public function activate(Request $request, EntityManagerInterface $entityManager, TachesRepository $tachesRepository, Taches $tache): Response
    {
        $tache->setStatut(false);
        $entityManager->flush();

        $this->addFlash('success', 'Tache confirmé avec succès');

        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);

    }
}
