<?php

namespace App\Controller;

use App\Entity\Photos;
use App\Form\PhotosFormType;
use App\Form\PhotosType;
use App\Repository\PhotosRepository;
use App\Services\UploadService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PhotosController extends AbstractController
{
    public function __construct(
        private UploadService $uploadService,
    )
    {

    }

    /**
     * Permet à un utilisateur de lister ces photos
     *
     * @param PhotosRepository $photosRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/gestion/mes-photos', name: 'app_photos_user', methods: ['GET']), IsGranted('ROLE_USER')]
    public function photoUser(PhotosRepository $photosRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('photos/liste.html.twig', [
            'photos' => $photosRepository->findPhotosUser($page, $this->getUser(), 5),
        ]);
    }
    #[Route('/gestion/photos', name: 'app_photos_index', methods: ['GET'])]
    public function index(PhotosRepository $photosRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('photos/liste.html.twig', [
            'photos' => $photosRepository->findPhotos($page, 5),
        ]);
    }
    #[Route('/gestion/photos/{id}/activation', name: 'app_photos_activate', methods: ['GET']), IsGranted('ROLE_USER')]
    public function activer(Request $request, Photos $photos, EntityManagerInterface $manager): Response
    {
        
        if ($this->getUser()->getRoles()[0] === 'ROLE_USER') {
            if ($photos->getUsers() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_photos_user', [], Response::HTTP_SEE_OTHER);

            }
            $routeRetour = 'app_photos_user';
        }
        else {
            $routeRetour = 'app_photos_index';
        }

        if ($photos->isStatut() === false) {
            $photos->setStatut(true);
            $manager->flush();
            $this->addFlash('success', 'Votre photo a bien été activé');
        }
        else {
            $photos->setStatut(false);
            $manager->flush();
            $this->addFlash('success', 'Votre photo a bien été désactivé');
        }

        
        return $this->redirectToRoute($routeRetour, [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/gestion/photos/{id}/suppression', name: 'app_photos_delete', methods: ['GET']), IsGranted('ROLE_USER')]
    public function delete(Request $request, Photos $photos, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getRoles()[0] === 'ROLE_USER') {
            if ($photos->getUsers() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_photos_user', [], Response::HTTP_SEE_OTHER);
            }
            $routeRetour = 'app_photos_user';
        }
        else {
            $routeRetour = 'app_photos_index';
        }

        unlink('/var/www/clients/client0/web2/web/public' . $photos->getUrl());

        $entityManager->remove($photos);
        $entityManager->flush();

        $this->addFlash('success', 'Votre photo a bien été supprimé');

        return $this->redirectToRoute($routeRetour, [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/gestion/photos/ajouter', name: 'app_photos_ajouter', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
    {
        $photo = new Photos();
        $form = $this->createForm(PhotosFormType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            



            if ($form->get('url')->getData()) {
                // On récupère l'image
                $fichier = $form->get('url')->getData();
                // On récupère le répertoire de destination
                $directory = 'portfolio_directory';
                // Puis on upload l'image'
                $photo->setUrl('/images/portfolio/' .$this->uploadService->send($fichier, $directory));
            }

            
            $photo->setCreatedAt(new DateTime('now'))
                  ->setUsers($this->getUser());
            //dd($this->getUser());

            //dd($photo);

            $response = $httpClient->request(
                'GET',
                'https://bot.team-occitanie.fr/post-photo/query/?username='.$photo->getUsers()->getUsername().'&avatar=https://www.team-occitanie.fr'.$photo->getUsers()->getAvatar().'&image=https://www.team-occitanie.fr'.$photo->getUrl().''
            );
            $content = $response->toArray();

            $photo->setDiscordId($content["messageId"]);

            dd($photo);

           // $entityManager->persist($photo);
            //$entityManager->flush();

            

            $this->addFlash('success', 'Votre photo a bien été envoyé');

            return $this->redirectToRoute('app_photos_ajouter', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('photos/new.html.twig', [
            'photo' => $photo,
            'form' => $form,
        ]);
    }

}
