<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use App\Repository\PhotosRepository;
use App\Repository\ArticlesRepository;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/portfolio', name: 'app_portfolio')]
    public function portfolio(Request $request, PhotosRepository $photosRepository, UsersRepository $usersRepository): Response
    {

        $page = $request->query->getInt('page', 1);

        
            $photos = $photosRepository->findPhotos($page, 15);

            //dd($request);

        return $this->render('home/portfolio.html.twig', [
            'photos' => $photos,
        ]);
    }

    
}
