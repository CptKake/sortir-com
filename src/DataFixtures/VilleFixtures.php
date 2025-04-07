<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VilleFixtures extends Fixture
{
    public const VILLE_NANTES = 'VILLE_NANTES';
    public const VILLE_RENNES = 'VILLE_RENNES';
    public const VILLE_NIORT = 'VILLE_NIORT';
    public const VILLE_QUIMPER = 'VILLE_QUIMPER';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Villes principales
        $mainCities = [
            self::VILLE_NANTES => ['Nantes', '44000'],
            self::VILLE_RENNES => ['Rennes', '35000'],
            self::VILLE_NIORT => ['Niort', '79000'],
            self::VILLE_QUIMPER => ['Quimper', '29000']
        ];

        foreach ($mainCities as $reference => [$nom, $codePostal]) {
            $ville = new Ville();
            $ville->setNom($nom);
            $ville->setCodePostal($codePostal);

            $manager->persist($ville);
            $this->addReference($reference, $ville);
        }

        // Villes supplémentaires pour avoir plus de diversité
        $additionalCities = [
            'Saint-Herblain' => '44800',
            'Orvault' => '44700',
            'Rezé' => '44400',
            'Saint-Sébastien-sur-Loire' => '44230',
            'Cesson-Sévigné' => '35510',
            'Bruz' => '35170',
            'Lorient' => '56100',
            'Vannes' => '56000'
        ];

        $i = 0;
        foreach ($additionalCities as $nom => $codePostal) {
            $ville = new Ville();
            $ville->setNom($nom);
            $ville->setCodePostal($codePostal);

            $manager->persist($ville);
            $this->addReference('VILLE_ADDITIONAL_' . $i, $ville);
            $i++;
        }

        $manager->flush();
    }
}