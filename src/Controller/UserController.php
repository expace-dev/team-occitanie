<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UserFormType;
use App\Form\UserInfoFormType;
use App\Services\UploadService;
use App\Repository\UsersRepository;
use App\Form\UserCredentialsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

    private UploadService $uploadService;
    private UserPasswordHasherInterface $encoder;

    public function __construct(UploadService $uploadService, UserPasswordHasherInterface $encoder)
    {
        $this->uploadService = $uploadService;
        $this->encoder = $encoder;
    }
    /**
     * Permet de lister les joueurs
     *
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/administration/utilisateurs', name: 'app_user_index', methods: ['GET']), IsGranted('ROLE_MODO')]
    public function index(UsersRepository $usersRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('user/index.html.twig', [
            'users' => $usersRepository->findUsers($page, 15),
        ]);
    }

    /**
     * Permet à un joueur de modifier sa fiche
     */
    #[Route('/profil/edition', name: 'app_user_edit_client', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function editProfil(Request $request, EntityManagerInterface $entityManager, UsersRepository $userRepository): Response
    {
        $user = $this->getUser();
        
        $formUserInfo = $this->createForm(UserInfoFormType::class, $user);
        $formUserInfo->handleRequest($request);

        if ($formUserInfo->isSubmitted() && $formUserInfo->isValid()) {

            // Si on réceptionne une image d'illustration
            if ($formUserInfo->get('avatar')->getData()) {

                if ($user->getAvatar()) {
                    unlink('/var/www/clients/client0/web2/web/public' . $user->getAvatar());
                }
                // On récupère l'image
                $fichier = $formUserInfo->get('avatar')->getData();
                // On récupère le répertoire de destination
                $directory = 'avatar_directory';
                // Puis on upload la nouvelle image et on ajoute cela à  l'article
                $user->setAvatar('/images/avatars/' .$this->uploadService->send($fichier, $directory));

            }

            $entityManager->flush();

            $this->addFlash('success', 'Ton profil a bien été mis à jour');

            return $this->redirectToRoute('app_user_edit_client', [], Response::HTTP_SEE_OTHER);
        }
        
        $url = $this->generateUrl('app_user_edit_client', [
            '_fragment' => 'credentials'
        ]);

        $formUserCredentials = $this->createForm(UserCredentialsFormType::class, $user);
        $formUserCredentials->handleRequest($request);

        if ($formUserCredentials->isSubmitted() && $formUserCredentials->isValid()) {

            $verifiedPassword = $this->encoder->isPasswordValid($user, $formUserCredentials->get('ancienPassword')->getData());

            if ($verifiedPassword === false) {
                    $this->addFlash('error', 'Ton mot de passe est invalide');
                    return $this->redirect($url);
            }
            if ($formUserCredentials->get('plainPassword')->getData()) {
                if ($formUserCredentials->get('plainPassword')->getData() === $formUserCredentials->get('ancienPassword')->getData()) {
                    $this->addFlash('error', 'Ton mot de passe correspond à celui enregistré');
                        return $this->redirect($url);
                }
                else {
                    $passwordEncoded = $this->encoder->hashPassword(
                    $user,
                    $formUserCredentials->get('plainPassword')->getData()
                    );
                    $user->setPassword($passwordEncoded);
                }
            }


            $entityManager->flush();

            $this->addFlash('success', 'Tes identifiants ont bien été mis à jour');

            return $this->redirect($url);

        }
        
        if ($formUserCredentials->isSubmitted() && !$formUserCredentials->isValid()) {
            $this->addFlash('error', 'Tu ne peux pas utiliser cette adresse Email');

            return $this->redirect($url);
        }


        return $this->render('user/edit_by_user.html.twig', [
            'user' => $user,
            'formUserInfo' => $formUserInfo,
            'formUserCredentials' => $formUserCredentials
        ]);
    }
    /**
     * Permet d'éditer la fiche d'un joueur
     */
    #[Route('/administration/{id}/edition', name: 'app_user_edit', methods: ['GET', 'POST']), IsGranted('ROLE_MODO')]
    public function edit(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {

        if ($this->getUser()->getRoles()[0] !== 'ROLE_ADMIN' && $user->getRole() === 'ROLE_ADMIN') {
            $this->addFlash('error', 'Vous ne pouvez pas éditer un administrateur');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Des erreurs subsistent, veuillez vérifier votre saisie !');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    /**
     * 
     * Permet à l'administrateur de supprimer un utilisateur
     * 
     */
    #[Route('/administration/utilisateurs/suppression/{id}', name: 'app_user_delete', methods: ['GET']), IsGranted('ROLE_MODO')]
    public function delete(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getRoles()[0] !== 'ROLE_ADMIN') {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette fonction');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Le joueur <b>' .$user->getUsername(). '</b> a bien été supprimé');

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
