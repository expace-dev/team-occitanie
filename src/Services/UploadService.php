<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploadService {

    /**
     *
     * @param ParameterBagInterface $params
     */
    public function __construct(private ParameterBagInterface $params)
    {
        
    }

    /**
     * Fonction permettant d'uploader un fichier
     *
     * @param [type] $fichier
     * @param [type] $directory
     * @return void
     */
    public function send($fichier, $directory) {
        
        $nom = md5(uniqid()) . '.' . $fichier->guessExtension();

        
        $fichier->move(
            $this->params->get($directory),
            $nom
        );
        
        return $nom;
    }
}