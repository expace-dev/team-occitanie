<?php

namespace App\Components;

use App\Repository\ArticlesRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('blogpostRecent')]
class BlogPostRecentComponent {

    private $articlesRepository;


    public function __construct(ArticlesRepository $articlesRepository)
    {
        $this->articlesRepository = $articlesRepository;
    }

    public function getRecentArticle(): array {

        return $this->articlesRepository->findBy([], ['date' => 'DESC'], 10);
    }
    
}