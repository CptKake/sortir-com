<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public const ETAT_CREEE = 'ETAT_CREEE';
    public const ETAT_OUVERTE = 'ETAT_OUVERTE';
    public const ETAT_CLOTUREE = 'ETAT_CLOTUREE';
    public const ETAT_ACTIVITE_EN_COURS = 'ETAT_ACTIVITE_EN_COURS';
    public const ETAT_PASSEE = 'ETAT_PASSEE';
    public const ETAT_ANNULEE = 'ETAT_ANNULEE';

    public function load(ObjectManager $manager): void
    {
        $etats = [
            self::ETAT_CREEE => 'Créée',
            self::ETAT_OUVERTE => 'Ouverte',
            self::ETAT_CLOTUREE => 'Clôturée',
            self::ETAT_ACTIVITE_EN_COURS => 'Activité en cours',
            self::ETAT_PASSEE => 'Passée',
            self::ETAT_ANNULEE => 'Annulée'
        ];

        foreach ($etats as $reference => $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);

            $manager->persist($etat);
            $this->addReference($reference, $etat);
        }

        $manager->flush();
    }
}