<?php

namespace App\Components;

use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('blogCategories')]
class BlogCategoriesComponent {

    private $categoriesRepository;
    protected $requestStack;


    public function __construct(CategoriesRepository $categoriesRepository, RequestStack $requestStack)
    {
        $this->categoriesRepository = $categoriesRepository;
        $this->requestStack = $requestStack;
    }

    public function getAllCategorie(): array {

        return $this->categoriesRepository->findAll();
    }
    
}