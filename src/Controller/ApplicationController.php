<?php

namespace App\Controller;

use App\Entity\Evenements;
use App\Entity\Taches;
use App\Form\EvenementsFormType;
use App\Form\TachesFormType;
use App\Repository\TachesRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApplicationController extends AbstractController
{
    #[Route('/application', name: 'app_application'), IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager, TachesRepository $tachesRepository): Response
    {
        $evenement = new Evenements();
        $formEvents = $this->createForm(EvenementsFormType::class, $evenement);
        $formEvents->handleRequest($request);

        if ($formEvents->isSubmitted() && $formEvents->isValid()) {
            dd($evenement);
        }
        
        $tache = new Taches();

        $formTache = $this->createForm(TachesFormType::class, $tache);
        $formTache->handleRequest($request);

        $page = $request->query->getInt('page', 1);

        $taches = $tachesRepository->findTaches($page, 15);

        if ($formTache->isSubmitted() && $formTache->isValid()) {

            
            $tache->setCreatedAt(new DateTime());
            $tache->setAuteur($this->getUser());

            $entityManager->persist($tache);
            $entityManager->flush();

            return $this->redirectToRoute('app_application', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('application/index.html.twig', [
            'tache' => $tache,
            'formTache' => $formTache,
            'taches' => $taches,
            'formEvents' => $formEvents
        ]);
    }
    #[Route('/application/tache-faite/{id}', name: 'app_application_validation'), IsGranted('ROLE_USER')]
    public function activate(Request $request, EntityManagerInterface $entityManager, TachesRepository $tachesRepository, Taches $tache): Response
    {
        $tache->setStatut(false);
        $entityManager->flush();

        $this->addFlash('success', 'Tache confirmé avec succès');

        return $this->redirectToRoute('app_application', [], Response::HTTP_SEE_OTHER);

    }
}
