<?php

namespace App\Components;

use App\Entity\Articles;
use App\Repository\ArticlesRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('blogpost')]
class BlogpostComponent {
    
    public int $id;

    public function __construct(private ArticlesRepository $articlesRepository)
    {
        
    }

    public function getBlogpost(): Articles {

        return $this->articlesRepository->find($this->id);

    }
}