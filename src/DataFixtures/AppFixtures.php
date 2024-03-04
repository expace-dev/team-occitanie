<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Users;
use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Commentaires;
use App\Entity\Evenements;
use App\Entity\Photos;
use App\Entity\Taches;
use App\Services\UploadService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{


    public function __construct(private UserPasswordHasherInterface $encoder, private UploadService $uploadService)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $adminUser = new Users();

        $adminUser->setEmail('mega-services@hotmail.fr')
                  ->setRole('ROLE_ADMIN')
                  ->setPassword($this->encoder->hashPassword($adminUser, 'Laura@14111990'))
                  ->setUsername('Frederic')
                  ->setIsVerified(1)
                  ->setAvatar('https://randomuser.me/api/portraits/men/29.jpg')
                  ->setDescription($faker->text(255))
                  ->setPoste('Gerant');

        $manager->persist($adminUser);

        $users = [];
        $postes = ['Chauffeur', 'Gerant', 'Agriculteur'];
        $genres = ['male', 'female'];
        $roles = ['ROLE_ADMIN', 'ROLE_USER', 'ROLE_MODO', 'ROLE_EDIT'];
        

        for ($i=0; $i<=50; $i++) {

            $user = new Users;
            $genre = $faker->randomElement($genres);
            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';
            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;
            
            $user->setEmail($faker->email())
                  ->setPassword($this->encoder->hashPassword($user, 'password'))
                  ->setUsername($faker->firstName())
                  ->setIsVerified(1)
                  ->setAvatar($picture)
                  ->setDescription($faker->text(255))
                  ->setPoste($faker->randomElement($postes))
                  ->setRole($faker->randomElement($roles));

            $manager->persist($user);
            $users[] = $user;
        }

        $categories = [];

        for ($i=1; $i<=10; $i++) {

            $categorie = new Categories;

            $mots = rand(1,3);

            $categorie->setNom($faker->words($mots, true));

            $manager->persist($categorie);
            $categories[] = $categorie;


        }

        $articles = [];
        $statut = [0, 1];

        for ($i=0; $i<=50; $i++) {

            $article = new Articles;

            $article->setImg($faker->imageUrl(1024, 768))
                    ->setTitre($faker->text(50))
                    ->setDate($faker->dateTime())
                    ->setContenu($faker->paragraph(5))
                    ->setIntroduction($faker->paragraph(2))
                    ->setAuteur($faker->randomElement($users))
                    ->setCategories($faker->randomElement($categories))
                    ->setActive($faker->randomElement($statut));

            $manager->persist($article);
            $articles[] = $article;


        }

        $commentaires = [];
        for ($i=1; $i<=500; $i++) {

            $commentaire = new Commentaires();

            $commentaire->setArticles($faker->randomElement($articles))
                        ->setAuteur($faker->randomElement($users))
                        ->setContenu($faker->paragraph(1))
                        ->setActive($faker->randomElement($statut))
                        ->setCreatedAt($faker->dateTime());

            $manager->persist($commentaire);
            $commentaires[] = $commentaire;
        }

        $reponses = [];
        for ($i=1; $i<=500; $i++) {

            $reponse = new Commentaires();

            $reponse->setArticles($faker->randomElement($articles))
                        ->setAuteur($faker->randomElement($users))
                        ->setContenu($faker->paragraph(1))
                        ->setActive($faker->randomElement($statut))
                        ->setCreatedAt($faker->dateTime())
                        ->setParent($faker->randomElement($commentaires));

            $manager->persist($reponse);
            $reponses[] = $reponse;
        }


        $photos = [];
        for ($i=1; $i<=500; $i++) {

            $photo = new Photos();

            $photo->setUrl($faker->imageUrl())
                    ->setStatut($faker->randomElement($statut))
                    ->setUsers($faker->randomElement($users))
                    ->setCreatedAt($faker->dateTime());

            $manager->persist($photo);
            $photos[] = $photo;
        }

        $evenements = [];
        $typesConvoi = ['Convoi perso', 'Trucker MP'];
        for ($i=1; $i<=500; $i++) {

            $evenement = new Evenements();

            $evenement->setAuteur($faker->randomElement($users))
                    ->setVisuel($faker->imageUrl())
                    ->setCreatedAt($faker->dateTime())
                    ->setDateEvents($faker->dateTime())
                    ->setTypeSession($faker->randomElement($typesConvoi))
                    ->setDescription($faker->paragraph(1));

            $manager->persist($evenement);
            $evenements[] = $evenement;
        }

        $taches = [];
        $mapsType = ['Castelnaud', 'Pallegney'];
        for ($i=1; $i<=500; $i++) {

            $tache = new Taches();

            $tache->setAuteur($faker->randomElement($users))
                    ->setType($faker->paragraph(1))
                    ->setCreatedAt($faker->dateTime())
                    ->setDelai($faker->dateTime())
                    ->setMap($faker->randomElement($mapsType))
                    ->setStatut($faker->randomElement($statut));

            $manager->persist($tache);
            $taches[] = $tache;
        }



        $manager->flush();
    }

}
