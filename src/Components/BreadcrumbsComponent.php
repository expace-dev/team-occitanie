<?php

namespace App\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('breadcrumbs')]
class BreadcrumbsComponent {
    
    public string $title;

    public function getTitle(): string {

        return $this->title;

    }

}