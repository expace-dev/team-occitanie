<?php

namespace App\Components;

use App\Repository\ArticlesRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('blogpostAll')]
class BlogpostAllComponent {

    private $articlesRepository;
    protected $requestStack;


    public function __construct(ArticlesRepository $articlesRepository, RequestStack $requestStack)
    {
        $this->articlesRepository = $articlesRepository;
        $this->requestStack = $requestStack;
    }

    public function getAllBlogpost(): array {

        $request = $this->requestStack->getCurrentRequest();
        
        $page = $request->query->getInt('page', 1);

        //$donnees = $articlesRepository->findArticles($page, 5);
        return $this->articlesRepository->findArticles($page, 5);
    }
    
}