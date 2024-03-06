<?php

namespace App\Controller;

use App\Services\UploadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploadController extends AbstractController
{
	const MAX_FILESIZE = 20000000; // 20 MB

	public function __construct(private UploadService $uploadService)
    {
    }

	#[Route('/mce/upload', name: 'app_upload_mce')]
	public function upload(Request $request): Response
	{
		 // @TODO : définissez votre vos propres domaines dans `$allowedOrigins`
		$allowedOrigins = ["127.0.0.1"];
		$origin = $request->server->get('REMOTE_ADDR');


		// Les requêtes de même origine ne définiront pas d'origine. Si l'origine est définie, elle doit être valide.
		if ($origin && !in_array($origin, $allowedOrigins)) {
			return new Response("Vous n'avez pas accès à cette ressource.", 403);
		}

		// Ne tente pas de traiter le téléchargement sur une requête OPTIONS
		if ($request->isMethod("OPTIONS")) {
			return new Response("", 200, ["Access-Control-Allow-Methods" => "POST, OPTIONS"]);
		}

		/** @var UploadedFile|null */
		$file = $request->files->get("file");

		if (!$file) {
			return new Response("Fichier manquant.", 400);
		}

		if ($file->getSize() > self::MAX_FILESIZE) {
			return new Response("Votre fichier est trop volumineux. Taille maximum: ".(self::MAX_FILESIZE / 1000000)."MB", 400);
		}

		if (!str_starts_with($file->getMimeType(), "image/")) {
			return new Response("Le fichier fourni n'est pas une image.", 400);
		}

		//dd($file);

		/**
		* @TODO : remplacez cette ligne par votre propre processus de téléchargement/enregistrement de fichiers.
		* La variable $fileUrl doit contenir l'URL accessible publiquement 
		*/

		//$fichier = $form->get('avatar')->getData();
        // On récupère le répertoire de destination
        $directory = 'blog_directory';
        // Puis on upload la nouvelle image et on ajoute cela à  l'article
        $fichier = $this->uploadService->send($file, $directory);

		$fileUrl = "https://www.team-occitanie.fr/images/blog/$fichier";

		return new JsonResponse(
			["location" => $fileUrl],
			200,
			[
				"Access-Control-Allow-Origin" => $origin,
				"Access-Control-Allow-Credentials" => true,
				"P3P" => 'CP="There is no P3P policy."',
			],
		);
	}
}