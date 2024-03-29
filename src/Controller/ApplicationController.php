<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Taches;
use App\Entity\Evenements;
use App\Form\TachesFormType;
use App\Services\UploadService;
use App\Form\EvenementsFormType;
use App\Repository\TachesRepository;
use App\Repository\EvenementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
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
    public function index(
        Request $request, 
        EntityManagerInterface $entityManager, 
        TachesRepository $tachesRepository, 
        EvenementsRepository $evenementsRepository,
        HttpClientInterface $httpClient
        ): Response
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


            if ($evenement->getVisuel()) {
                $visuel = 'https://www.team-occitanie.fr/'.$evenement->getVisuel().'';
            }
            else {
                $visuel = 'https://www.team-occitanie.fr/images/evenements/no_visuel.png';
            }
            
            $response2 = $httpClient->request(
                'GET',
                'https://bot.team-occitanie.fr/add-evenement/query/?username='.$evenement->getAuteur()->getUsername().'&date='.$date.'&description='.$evenement->getDescription().'&avatar='.$evenement->getAuteur()->getAvatar().'&type='.$evenement->getTypeSession().'&image='.$visuel.'',
                [
                    'headers' => [
                        'Origin' => 'https://www.team-occitanie.fr'
                    ],  
                ]
            );
        
            $content2 = $response2->toArray();

            
        
            $evenement->setDiscordId($content2["messageId"]);

            
            $entityManager->persist($evenement);
            $entityManager->flush();

            

            $this->addFlash('sucess', 'Votre évènement a bien été ajouté');

            $url = $this->generateUrl('app_application_index', [
                '_fragment' => 'ets'
            ]);

            return $this->redirect($url, Response::HTTP_SEE_OTHER);
            
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

            $response = $httpClient->request(
                'GET',
                'https://bot.team-occitanie.fr/add-tache/query/?username='.$tache->getAuteur()->getUsername().'&map='.$tache->getMap().'&description='.$tache->getType().'&avatar='.$tache->getAuteur()->getAvatar().'',
                [
                    'headers' => [
                        'Origin' => 'https://www.team-occitanie.fr'
                    ],  
                ]
            );

            $content = $response->toArray();

            $tache->setDiscordId($content["messageId"]);

            $entityManager->persist($tache);
            $entityManager->flush();

            $this->addFlash('sucess', 'Votre tâche a bien été ajouté');

            $url = $this->generateUrl('app_application_index', [
                '_fragment' => 'farming'
            ]);

            return $this->redirect($url, Response::HTTP_SEE_OTHER);

        }

        $maintenant = new DateTime();

        return $this->render('application/index.html.twig', [
            'tache' => $tache,
            'formTache' => $formTache,
            'taches' => $tachesRepository->findBy(['statut' => true]),
            'formEvents' => $formEvents,
            'evenements' => $evenementsRepository->findFuturEvents($maintenant)
        ]);
    }
    #[Route('/application/validation/tache/{id}', name: 'app_application_validation'), IsGranted('ROLE_USER')]
    public function activate(Request $request, EntityManagerInterface $entityManager, TachesRepository $tachesRepository, Taches $tache, HttpClientInterface $httpClient): Response
    {

        $httpClient->request(
            'GET',
            'https://bot.team-occitanie.fr/remove-tache/query/?id='.$tache->getDiscordId().'',
            [
                'headers' => [
                    'Origin' => 'https://www.team-occitanie.fr'
                ],  
            ]
        );

        $entityManager->remove($tache);
        $entityManager->flush();

        $this->addFlash('success', 'Tache confirmé avec succès');

        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);

    }
    #[Route('/gestion/evenements', name: 'app_evenements_index'), IsGranted('ROLE_USER')]
    public function evenements(Request $request, EvenementsRepository $evenementsRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        
        if ($this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {
            $evenements = $evenementsRepository->findEvenements($page, 15);
        }
        else {
            $evenements = $evenementsRepository->findEvenementsAuteur($page, $this->getUser(), 15);
        }
        

        return $this->render('evenements/liste.html.twig', [
            'evenements' => $evenements,
        ]);

    }
    #[Route('/gestion/evenements/{id}/suppression', name: 'app_evenements_delete', methods: ['GET']), IsGranted('ROLE_USER')]
    public function deleteEvenements(Request $request, Evenements $evenement, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
    {
        if ($this->getUser()->getRoles()[0] === 'ROLE_USER') {
            if ($evenement->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        if ($evenement->getVisuel()) {
            unlink('/var/www/clients/client0/web2/web/public' . $evenement->getVisuel());
        }

        

        $httpClient->request(
            'GET',
            'https://bot.team-occitanie.fr/remove-evenement/query/?id='.$evenement->getDiscordId().'',
            [
                'headers' => [
                    'Origin' => 'https://www.team-occitanie.fr'
                ],  
            ]
        );

        $entityManager->remove($evenement);
        $entityManager->flush();

        $this->addFlash('success', 'Votre evenement a bien été supprimé');

        return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/gestion/evenements/{id}/edition', name: 'app_evenements_edit', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function editEvenements(Request $request, Evenements $evenement, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
    {

        if ($this->getUser()->getRoles()[0] === 'ROLE_USER') {
            if ($evenement->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(EvenementsFormType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date = $form->get('dateEvents')->getData();
            $dateCreate = new DateTime($date);
            
            $evenement->setDateEvents($dateCreate);

            


            // Si on réceptionne une image d'illustration
            if ($form->get('visuel')->getData()) {
                // On récupère l'image
                $fichier = $form->get('visuel')->getData();
                // On récupère le répertoire de destination
                $directory = 'events_directory';
                // On supprime l'ancienne image d'illustration
                if ($evenement->getVisuel()) {
                    unlink('/var/www/clients/client0/web2/web/public' .$evenement->getVisuel());
                }
                // Puis on upload la nouvelle image et on ajoute cela à  l'article
                $evenement->setVisuel('/images/evenements/' .$this->uploadService->send($fichier, $directory));
            }


            if ($evenement->getVisuel()) {
                $visuel = 'https://www.team-occitanie.fr/'.$evenement->getVisuel().'';
            }
            else {
                $visuel = 'https://www.team-occitanie.fr/images/evenements/no_visuel.png';
            }
           

            $response2 = $httpClient->request(
                'GET',
                'https://bot.team-occitanie.fr/edit-evenement/query/?username='.$evenement->getAuteur()->getUsername().'&date='.$date.'&description='.$evenement->getDescription().'&avatar='.$evenement->getAuteur()->getAvatar().'&type='.$evenement->getTypeSession().'&image='.$visuel.'&id='.$evenement->getDiscordId().'',
                [
                    'headers' => [
                        'Origin' => 'https://www.team-occitanie.fr'
                    ],  
                ]
            );

            $this->addFlash('success', 'Votre evenement a bien été modifié');

            $entityManager->flush();

            return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Des erreurs subsistent, veuillez vérifier votre saisie');
        }

        return $this->render('evenements/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
            'dateEvents' => $evenement->getDateEvents()
        ]);
    }












    #[Route('/gestion/taches', name: 'app_taches_index'), IsGranted('ROLE_USER')]
    public function taches(Request $request, TachesRepository $tachesRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        
        if ($this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {
            $taches = $tachesRepository->findTaches($page, 15);
        }
        else {
            $taches = $tachesRepository->findTachesAuteur($page, $this->getUser(), 15);
        }
        

        return $this->render('taches/liste.html.twig', [
            'taches' => $taches,
        ]);

    }
    #[Route('/gestion/taches/{id}/suppression', name: 'app_taches_delete', methods: ['GET']), IsGranted('ROLE_USER')]
    public function deleteTaches(Request $request, Taches $taches, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getRoles()[0] === 'ROLE_USER') {
            if ($taches->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_taches_index', [], Response::HTTP_SEE_OTHER);
            }
        }


        $entityManager->remove($taches);
        $entityManager->flush();

        $this->addFlash('success', 'Votre tache a bien été supprimé');

        return $this->redirectToRoute('app_taches_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/gestion/taches/{id}/edition', name: 'app_taches_edit', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function editTaches(Request $request, Taches $taches, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
    {

        if ($this->getUser()->getRoles()[0] === 'ROLE_USER') {
            if ($taches->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_taches_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(TachesFormType::class, $taches);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date = $form->get('delai')->getData();
            $dateCreate = new DateTime($date);
            $taches->setDelai($dateCreate);

            $response = $httpClient->request(
                'GET',
                'https://bot.team-occitanie.fr/edit-tache/query/?username='.$taches->getAuteur()->getUsername().'&map='.$taches->getMap().'&description='.$taches->getType().'&avatar='.$taches->getAuteur()->getAvatar().'&id='.$taches->getDiscordId().'',
                [
                    'headers' => [
                        'Origin' => 'https://www.team-occitanie.fr'
                    ],  
                ]
            );



            $this->addFlash('success', 'Votre tache a bien été modifié');

            $entityManager->flush();

            return $this->redirectToRoute('app_taches_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Des erreurs subsistent, veuillez vérifier votre saisie');
        }

        return $this->render('taches/edit.html.twig', [
            'taches' => $taches,
            'form' => $form,
            'delai' => $taches->getDelai()
        ]);
    }
}
