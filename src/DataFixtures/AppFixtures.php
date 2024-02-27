<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\Users;
use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Commentaires;
use App\Entity\Photos;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
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



        $manager->flush();
    }

}
