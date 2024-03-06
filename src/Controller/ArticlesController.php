<?php

namespace App\Controller;

use DateTime;
use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Commentaires;
use App\Form\ArticlesFormType;
use App\Services\UploadService;
use App\Form\CategoriesFormType;
use App\Form\CommentairesFormType;
use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticlesController extends AbstractController
{
    public function __construct(
        private UploadService $uploadService,
    )
    {

    }

    /**
     * Permet d'afficher la liste des publications
     * 
     * uniquement accessible aux roles suivants:
     * 
     * ROLE_EDIT
     * ROLE_ADMIN
     *
     * @param ArticlesRepository $articlesRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/gestion/publications', name: 'app_admin_publi_index',  methods: ['GET']), IsGranted('ROLE_EDIT')]
    public function liste(ArticlesRepository $articlesRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        
        if ($this->getUser()->getRoles()[0] == 'ROLE_ADMIN' or $this->getUser()->getRoles()[0] == 'ROLE_MODO') {
            $articles = $articlesRepository->findArticles($page, 15);
        }
        else {
            $articles = $articlesRepository->findArticlesAuteur($page, $this->getUser(), 15);
        }
        

        return $this->render('articles/liste.html.twig', [
            'articles' => $articles,
        ]);
    }
    /**
     * Permet de modifier des publications
     * 
     * Uniquempent accessibles aux roles suivants
     * 
     * ROLE_EDIT
     * ROLE_ADMIN
     * 
     */
    #[Route('/gestion/publications/{id}/edit', name: 'app_articles_edit', methods: ['GET', 'POST']), IsGranted('ROLE_EDIT')]
    public function edit(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {

        if ($this->getUser()->getRoles()[0] === 'ROLE_EDIT') {
            if ($article->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(ArticlesFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            // Si on réceptionne une image d'illustration
            if ($form->get('img')->getData()) {
                // On récupère l'image
                $fichier = $form->get('img')->getData();
                // On récupère le répertoire de destination
                $directory = 'blog_directory';
                // On supprime l'ancienne image d'illustration
                unlink($article->getImg());
                // Puis on upload la nouvelle image et on ajoute cela à  l'article
                $article->setImg('/images/blog/' .$this->uploadService->send($fichier, $directory));
            }

            $this->addFlash('success', 'Votre article a bien été modifié');

            $entityManager->flush();

            return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Des erreurs subsistent, veuillez vérifier votre saisie');
        }

        return $this->render('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
    /**
     * Permet de lister les publications
     *
     * @return Response
     */
    #[Route('/publications', name: 'app_blog_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('articles/index.html.twig');
    }

    /**
     * Permet de lister les publications d'une catégorie
     * 
     * @return Response
     */
    #[Route('/publications/categorie/{slug}', name: 'app_blog_categories', methods: ['GET'])]
    public function categoriesAnnonce(): Response
    {
        return $this->render('articles/index.html.twig');
    }
    /**
     * Permet de créer une publication
     * 
     * Uniquement accessible aux roles suivants
     * 
     * ROLE_EDIT
     * ROLE_ADMIN
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/gestion/publications/creation', name: 'app_articles_new', methods: ['GET', 'POST']), IsGranted('ROLE_EDIT')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        dd($request->server->get('HTTP_HOST'));
        $article = new Articles();
        $form = $this->createForm(ArticlesFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Si on réceptionne une image d'illustration
            if ($form->get('img')->getData()) {
                // On récupère l'image
                $fichier = $form->get('img')->getData();
                // On récupère le répertoire de destination
                $directory = 'blog_directory';
                // On supprime l'ancienne image d'illustration
                //unlink($article->getImg());
                // Puis on upload la nouvelle image et on ajoute cela à  l'article
                $article->setImg('/images/blog/' .$this->uploadService->send($fichier, $directory));
            }

            $article->setAuteur($this->getUser());

            $article->setDate(new DateTime());
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
    /**
     * Permet d'afficher un article en particulier
     * et de poster un commentaire
     * 
     */
    #[Route('/publications/{slug}', name: 'app_blog_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Articles $article, EntityManagerInterface $manager): Response
    {
        $commentaires = new Commentaires();
        $form = $this->createForm(CommentairesFormType::class, $commentaires);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $commentaires->setCreatedAt(new DateTime());
            $commentaires->setArticles($article);
            $commentaires->setAuteur($this->getUser());
            $commentaires->setActive(1);


            // On récupère le contenu du champ parentid
            $parentid = $form->get("parentid")->getData();

            // On va chercher le commentaire correspondant
            if($parentid != null){
                $parent = $manager->getRepository(Commentaires::class)->find($parentid);
            }

            // On définit le parent
            $commentaires->setParent($parent ?? null);

            $manager->persist($commentaires);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été publié');

            $form = $this->createForm(CommentairesFormType::class);

        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Votre commentaire n\'as pas été publié');
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article,
            'form' => $form
        ]);
    }
    /** 
     * Permet d'activer ou désactiver une publication
     * 
     * Uniquement accessible aux roles suivants
     * 
     * ROLE_EDIT
     * ROLE_ADMIN
     * 
     * 
     */
    #[Route('/gestion/publications/{id}/activation', name: 'app_publi_activate', methods: ['GET']), IsGranted('ROLE_EDIT')]
    public function activer(Request $request, Articles $article, EntityManagerInterface $manager): Response
    {
        
        if ($this->getUser()->getRoles()[0] === 'ROLE_EDIT') {
            if ($article->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        if ($article->isActive() === false) {
            $article->setActive(true);
            $manager->flush();
            $this->addFlash('success', 'La publication a bien été activé');
        }
        else {
            $article->setActive(false);
            $manager->flush();
            $this->addFlash('success', 'La publication a bien été désactivé');
        }

        
        return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * Permet de supprimer une publication
     * 
     * uniquement accessible aux roles suivants
     * 
     * ROLE_EDIT
     * ROLE_ADMIN
     */
    #[Route('/gestion/publications/{id}/suppression', name: 'app_publi_delete', methods: ['GET']), IsGranted('ROLE_EDIT')]
    public function delete(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getRoles()[0] === 'ROLE_EDIT') {
            if ($article->getAuteur() !== $this->getUser()) {
                $this->addFlash('error', 'Une erreur c\'est produite, veuillez réessayer !');
                return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        if ($this->getUser()->getRoles()[0] === 'ROLE_MODO') {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette fonction');
            return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
        }



        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'La publication a bien été supprimé');

        return $this->redirectToRoute('app_admin_publi_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * Permet à l'administrateur de lister les catégories
     *
     * @param Request $request
     * @param CategoriesRepository $categoriesRepository
     * @return Response
     */
    #[Route('/gestion/categories', name: 'app_admin_categories_index', methods: ['GET']), IsGranted('ROLE_ADMIN')]
    public function addCat(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        ;
        $page = $request->query->getInt('page', 1);

        $categories = $categoriesRepository->findCategories($page, 15);


        return $this->render('articles/categories_liste.html.twig', [
            'categories' => $categories,
        ]);
    }
    #[Route('/gestion/categories/ajouter', name: 'app_admin_categories_new', methods: ['GET', 'POST']), IsGranted('ROLE_ADMIN')]
    public function categoriesNew(Request $request, EntityManagerInterface $manager): Response
    {
        $categorie = new Categories();
        $form = $this->createForm(CategoriesFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->persist($categorie);
            $manager->flush();

            $this->addFlash('success', 'Votre catégorie a été enregistré avec succès');

            return $this->redirectToRoute('app_admin_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/articles/categories_new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }
    #[Route('/gestion/categories/{id}/edition', name: 'app_admin_categories_edit', methods: ['GET', 'POST']), IsGranted('ROLE_ADMIN')]
    public function CategoriesEdit(Request $request, Categories $categorie, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CategoriesFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($categorie);
            $manager->flush();

            $this->addFlash('success', 'Votre catégorie a été enregistré avec succès');

            return $this->redirectToRoute('app_admin_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles/categories_edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);

    }
    #[Route('/gestion/categories/delete/{id}', name: 'app_admin_categories_delete', methods: ['GET']), IsGranted('ROLE_ADMIN')]
    public function CategoriesDelete(Categories $categorie, EntityManagerInterface $manager): Response
    {
        $manager->remove($categorie);
        $manager->flush();

        $this->addFlash('success', 'Votre catégorie a été supprimmé avec succès');

        return $this->redirectToRoute('app_admin_categories_index', [], Response::HTTP_SEE_OTHER);
    }

}
