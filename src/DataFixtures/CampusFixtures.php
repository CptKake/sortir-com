<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public const CAMPUS_NANTES = 'CAMPUS_NANTES';
    public const CAMPUS_RENNES = 'CAMPUS_RENNES';
    public const CAMPUS_NIORT = 'CAMPUS_NIORT';
    public const CAMPUS_QUIMPER = 'CAMPUS_QUIMPER';

    public function load(ObjectManager $manager): void
    {
        $campusData = [
            self::CAMPUS_NANTES => 'ENI Nantes',
            self::CAMPUS_RENNES => 'ENI Rennes',
            self::CAMPUS_NIORT => 'ENI Niort',
            self::CAMPUS_QUIMPER => 'ENI Quimper'
        ];

        foreach ($campusData as $reference => $nom) {
            $campus = new Campus();
            $campus->setNom($nom);

            $manager->persist($campus);
            $this->addReference($reference, $campus);
        }

        $manager->flush();
    }
}