<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d’un utilisateur de type “utilisateur” 
        $user = new User();
        $user->setEmail('jacques.saumon@gmoule.com'); //On peut mettre un mail bidon
        $user->setRoles(['ROLE_CONTRIBUTOR']); //On met un role
        $user->setAge('34'); //Un age
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'user'
        ); // On hash le mot de passe

        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // Création d’un utilisateur de type “administrateur” et on fait pareil
        $admin = new User();
        $admin->setEmail('admin@crazybox.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setAge('87');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin'
        );
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);


        $manager->flush();
    }
}
