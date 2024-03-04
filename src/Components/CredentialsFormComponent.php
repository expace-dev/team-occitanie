<?php

namespace App\Components;

use App\Entity\Users;
use App\Form\UserCredentialsFormType;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent('credentialsForm')]
class CredentialsFormComponent extends AbstractController {

    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(fieldName: 'data')]
    public ?Users $user = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UserCredentialsFormType::class, $this->user);
    }
    
}