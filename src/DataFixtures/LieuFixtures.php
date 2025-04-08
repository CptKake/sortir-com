<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Définition des villes principales avec leurs codes postaux
        $villes = [
            'NANTES' => ['nom' => 'Nantes', 'codePostal' => '44000'],
            'RENNES' => ['nom' => 'Rennes', 'codePostal' => '35000'],
            'NIORT' => ['nom' => 'Niort', 'codePostal' => '79000'],
            'QUIMPER' => ['nom' => 'Quimper', 'codePostal' => '29000'],
            'SAINT_HERBLAIN' => ['nom' => 'Saint-Herblain', 'codePostal' => '44800'],
            'ORVAULT' => ['nom' => 'Orvault', 'codePostal' => '44700'],
            'REZE' => ['nom' => 'Rezé', 'codePostal' => '44400'],
            'SAINT_SEBASTIEN' => ['nom' => 'Saint-Sébastien-sur-Loire', 'codePostal' => '44230'],
            'CESSON_SEVIGNE' => ['nom' => 'Cesson-Sévigné', 'codePostal' => '35510'],
            'BRUZ' => ['nom' => 'Bruz', 'codePostal' => '35170'],
            'LORIENT' => ['nom' => 'Lorient', 'codePostal' => '56100'],
            'VANNES' => ['nom' => 'Vannes', 'codePostal' => '56000']
        ];

        // Lieux pour Nantes
        $lieuxNantes = [
            ['Parc du Grand Blottereau', '47.2375', '-1.5217'],
            ['Château des Ducs de Bretagne', '47.2166', '-1.5485'],
            ['Lieu Unique', '47.2151', '-1.5450'],
            ['Pub O\'Flaherty\'s', '47.2134', '-1.5606'],
            ['Bowling de Nantes', '47.2389', '-1.5686']
        ];

        foreach ($lieuxNantes as $i => [$nom, $lat, $lon]) {
            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue($faker->streetAddress());
            $lieu->setLatitude((float)$lat);
            $lieu->setLongitude((float)$lon);
            $lieu->setVille($villes['NANTES']['nom']);
            $lieu->setCodePostal($villes['NANTES']['codePostal']);

            $manager->persist($lieu);
            $this->addReference('LIEU_NANTES_' . $i, $lieu);
        }

        // Lieux pour Rennes
        $lieuxRennes = [
            ['Parc du Thabor', '48.1135', '-1.6694'],
            ['Le 1988 Live Club', '48.1128', '-1.6789'],
            ['Laser Game Evolution', '48.0919', '-1.6827'],
            ['Bowling Alma', '48.1026', '-1.6744']
        ];

        foreach ($lieuxRennes as $i => [$nom, $lat, $lon]) {
            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue($faker->streetAddress());
            $lieu->setLatitude((float)$lat);
            $lieu->setLongitude((float)$lon);
            $lieu->setVille($villes['RENNES']['nom']);
            $lieu->setCodePostal($villes['RENNES']['codePostal']);

            $manager->persist($lieu);
            $this->addReference('LIEU_RENNES_' . $i, $lieu);
        }

        // Lieux pour Niort
        $lieuxNiort = [
            ['Parc de Pré-Leroy', '46.3292', '-0.4664'],
            ['L\'Acclameur', '46.3249', '-0.4349'],
            ['Cinéma CGR', '46.3246', '-0.4589']
        ];

        foreach ($lieuxNiort as $i => [$nom, $lat, $lon]) {
            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue($faker->streetAddress());
            $lieu->setLatitude((float)$lat);
            $lieu->setLongitude((float)$lon);
            $lieu->setVille($villes['NIORT']['nom']);
            $lieu->setCodePostal($villes['NIORT']['codePostal']);

            $manager->persist($lieu);
            $this->addReference('LIEU_NIORT_' . $i, $lieu);
        }

        // Lieux pour Quimper
        $lieuxQuimper = [
            ['Théâtre de Cornouaille', '47.9977', '-4.1008'],
            ['Multiplexe Cinéville', '47.9849', '-4.0927'],
            ['Bowling de Quimper', '47.9756', '-4.0858']
        ];

        foreach ($lieuxQuimper as $i => [$nom, $lat, $lon]) {
            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue($faker->streetAddress());
            $lieu->setLatitude((float)$lat);
            $lieu->setLongitude((float)$lon);
            $lieu->setVille($villes['QUIMPER']['nom']);
            $lieu->setCodePostal($villes['QUIMPER']['codePostal']);

            $manager->persist($lieu);
            $this->addReference('LIEU_QUIMPER_' . $i, $lieu);
        }

        // Lieux supplémentaires dans les villes additionnelles
        $additionalVilles = array_slice(array_keys($villes), 4); // Prend les villes à partir de l'index 4

        foreach ($additionalVilles as $i => $villeKey) {
            for ($j = 0; $j < 2; $j++) {
                $lieu = new Lieu();
                $lieu->setNom($faker->company());
                $lieu->setRue($faker->streetAddress());
                $lieu->setLatitude($faker->latitude(46.0, 48.5));
                $lieu->setLongitude($faker->longitude(-2.0, -0.5));
                $lieu->setVille($villes[$villeKey]['nom']);
                $lieu->setCodePostal($villes[$villeKey]['codePostal']);

                $manager->persist($lieu);
                $this->addReference('LIEU_ADDITIONAL_' . $i . '_' . $j, $lieu);
            }
        }

        $manager->flush();
    }
}