<?php

namespace App\Components;

use App\Repository\CommentairesRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsTwigComponent('blogCommentaires')]
class BlogCommentairesComponent extends AbstractController {
    
    public int $id;

    public function __construct(private CommentairesRepository $commentsRepository)
    {
        
    }



    public function getCommentaires() {
        
        
        return $this->commentsRepository->findBy(['articles' => $this->id]);
    }
}