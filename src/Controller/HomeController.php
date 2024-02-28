<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\UsersRepository;
use App\Repository\PhotosRepository;
use App\Repository\ArticlesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticlesRepository $articlesRepository, PhotosRepository $photosRepository, UsersRepository $usersRepository): Response
    {
            $articles = $articlesRepository->findBy(['active' => true], ['id' => 'ASC',], 3);
            $photos = $photosRepository->findBy(['statut' => true], ['id' => 'ASC',], 9);
            $membres = $usersRepository->findAll();


        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'photos' => $photos,
            'membres' => $membres
        ]);
    }

    
}
