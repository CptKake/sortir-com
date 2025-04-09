<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création de l'administrateur
        $admin = new Participant();
        $admin->setNom('Admin');
        $admin->setPrenom('Admin');
        $admin->setTelephone('0123456789');
        $admin->setEmail('admin@sortir.com');
        $admin->setPseudo('admin');
        $admin->setAdministrateur(true);
        $admin->setActif(true);
        $admin->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
        $admin->setMotDePasse($hashedPassword);

        $admin->setCampus($this->getReference(CampusFixtures::CAMPUS_NANTES, Campus::class));

        $admin->setUrlPhoto('défaut.png');

        $manager->persist($admin);
        $this->addReference('PARTICIPANT_ADMIN', $admin);

        // Création des participants réguliers
        $campusRefs = [
            CampusFixtures::CAMPUS_NANTES,
            CampusFixtures::CAMPUS_RENNES,
            CampusFixtures::CAMPUS_NIORT,
            CampusFixtures::CAMPUS_QUIMPER
        ];

        for ($i = 0; $i < 30; $i++) {
            $participant = new Participant();
            $participant->setNom($faker->lastName());
            $participant->setPrenom($faker->firstName());
            $participant->setTelephone($faker->phoneNumber());

            $email = strtolower($participant->getPrenom() . '.' . $participant->getNom() . '@sortir.com');
            $email = preg_replace('/[^a-z0-9@.]/', '', $email);
            $participant->setEmail($email);

            $pseudo = strtolower($participant->getPrenom() . substr($participant->getNom(), 0, 1) . $faker->randomNumber(2));
            $participant->setPseudo($pseudo);

            $participant->setAdministrateur(false);
            $participant->setActif(true);

            $participant->setUrlPhoto('défaut.png');

            $password = 'password';
            $hashedPassword = $this->passwordHasher->hashPassword($participant, $password);
            $participant->setMotDePasse($hashedPassword);

            // Assigner un campus aléatoire
            $campusRef = $faker->randomElement($campusRefs);
            $participant->setCampus($this->getReference($campusRef, Campus::class));

            $manager->persist($participant);
            $this->addReference('PARTICIPANT_' . $i, $participant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}