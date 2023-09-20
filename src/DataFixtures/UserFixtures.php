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
        $user->setEmail('jacques.saumon@gmoule.com');
        $user->setRoles(['ROLE_CONTRIBUTOR']);
        $user->setAge('34');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'user'
        );

        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // Création d’un utilisateur de type “administrateur”
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
