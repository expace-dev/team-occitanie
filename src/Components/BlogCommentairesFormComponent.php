<?php

namespace App\Components;

use App\Entity\Commentaires;
use App\Entity\Comments;
use App\Form\Blog\Client\CommentType;
use App\Form\CommentairesFormType;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent('blogCommentairesForm')]
class BlogCommentairesFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(fieldName: 'data')]
    public ?Commentaires $commentaires = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CommentairesFormType::class, $this->commentaires);
    }
}