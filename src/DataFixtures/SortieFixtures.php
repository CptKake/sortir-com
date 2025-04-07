<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer diverses sorties dans différents états

        // 1. Sorties passées
        $this->createPastSorties($manager, $faker, 15);

        // 2. Sorties actives (ouvertes)
        $this->createActiveSorties($manager, $faker, 10);

        // 3. Sorties à venir mais clôturées
        $this->createClosedSorties($manager, $faker, 5);

        // 4. Sorties en création
        $this->createDraftSorties($manager, $faker, 5);

        // 5. Sorties annulées
        $this->createCancelledSorties($manager, $faker, 5);

        $manager->flush();
    }

    private function createPastSorties(ObjectManager $manager, $faker, $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $sortie = new Sortie();
            $sortie->setNom('Sortie passée: ' . $faker->words(3, true));

            // Date dans le passé
            $dateDebut = $faker->dateTimeBetween('-6 months', '-1 week');
            $sortie->setDateHeureDebut($dateDebut);

            $duree = $faker->numberBetween(30, 240);
            $sortie->setDuree($duree);

            // Date d'inscription dans le passé
            $dateLimiteInscription = clone $dateDebut;
            $dateLimiteInscription->modify('-1 week');
            $sortie->setDateLimiteInscription($dateLimiteInscription);

            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 20));
            $sortie->setInfosSortie($faker->paragraph());

            // Assigner un organisateur aléatoire
            $participantId = $faker->numberBetween(0, 29);
            $sortie->setOrganisateur($this->getReference('PARTICIPANT_' . $participantId, Participant::class));

            // Assigner le même campus que l'organisateur
            $sortie->setCampus($sortie->getOrganisateur()->getCampus());

            // Assigner un lieu
            $campusName = $sortie->getCampus()->getNom();
            $lieuRef = $this->getLieuRefForCampus($campusName, $faker);
            $sortie->setLieu($this->getReference($lieuRef, Lieu::class));

            // Définir l'état comme "Passée"
            $sortie->setEtat($this->getReference(EtatFixtures::ETAT_PASSEE, Etat::class));

            $manager->persist($sortie);
            $this->addReference('SORTIE_PASSEE_' . $i, $sortie);
        }
    }

    private function createActiveSorties(ObjectManager $manager, $faker, $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $sortie = new Sortie();
            $sortie->setNom('Sortie à venir: ' . $faker->words(3, true));

            // Date dans le futur
            $dateDebut = $faker->dateTimeBetween('+1 week', '+3 months');
            $sortie->setDateHeureDebut($dateDebut);

            $duree = $faker->numberBetween(60, 240);
            $sortie->setDuree($duree);

            // Date d'inscription dans le futur mais avant la date de la sortie
            $dateLimiteInscription = clone $dateDebut;
            $dateLimiteInscription->modify('-1 day');
            $sortie->setDateLimiteInscription($dateLimiteInscription);

            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 20));
            $sortie->setInfosSortie($faker->paragraph());

            // Assigner un organisateur aléatoire
            $participantId = $faker->numberBetween(0, 29);
            $sortie->setOrganisateur($this->getReference('PARTICIPANT_' . $participantId, Participant::class));

            // Assigner le même campus que l'organisateur
            $sortie->setCampus($sortie->getOrganisateur()->getCampus());

            // Assigner un lieu
            $campusName = $sortie->getCampus()->getNom();
            $lieuRef = $this->getLieuRefForCampus($campusName, $faker);
            $sortie->setLieu($this->getReference($lieuRef, Lieu::class));

            // Définir l'état comme "Ouverte"
            $sortie->setEtat($this->getReference(EtatFixtures::ETAT_OUVERTE, Etat::class));

            $manager->persist($sortie);
            $this->addReference('SORTIE_ACTIVE_' . $i, $sortie);
        }
    }

    private function createClosedSorties(ObjectManager $manager, $faker, $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $sortie = new Sortie();
            $sortie->setNom('Sortie complète: ' . $faker->words(3, true));

            // Date dans le futur
            $dateDebut = $faker->dateTimeBetween('+1 week', '+2 months');
            $sortie->setDateHeureDebut($dateDebut);

            $duree = $faker->numberBetween(60, 180);
            $sortie->setDuree($duree);

            // Date d'inscription passée
            $dateLimiteInscription = new \DateTime('yesterday');
            $sortie->setDateLimiteInscription($dateLimiteInscription);

            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 10));
            $sortie->setInfosSortie($faker->paragraph());

            // Assigner un organisateur aléatoire
            $participantId = $faker->numberBetween(0, 29);
            $sortie->setOrganisateur($this->getReference('PARTICIPANT_' . $participantId, Participant::class));

            // Assigner le même campus que l'organisateur
            $sortie->setCampus($sortie->getOrganisateur()->getCampus());

            // Assigner un lieu
            $campusName = $sortie->getCampus()->getNom();
            $lieuRef = $this->getLieuRefForCampus($campusName, $faker);
            $sortie->setLieu($this->getReference($lieuRef, Lieu::class));

            // Définir l'état comme "Clôturée"
            $sortie->setEtat($this->getReference(EtatFixtures::ETAT_CLOTUREE, Etat::class));

            $manager->persist($sortie);
            $this->addReference('SORTIE_CLOTUREE_' . $i, $sortie);
        }
    }

    private function createDraftSorties(ObjectManager $manager, $faker, $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $sortie = new Sortie();
            $sortie->setNom('Sortie en création: ' . $faker->words(3, true));

            // Date dans le futur lointain
            $dateDebut = $faker->dateTimeBetween('+2 months', '+4 months');
            $sortie->setDateHeureDebut($dateDebut);

            $duree = $faker->numberBetween(60, 240);
            $sortie->setDuree($duree);

            // Date d'inscription dans le futur
            $dateLimiteInscription = clone $dateDebut;
            $dateLimiteInscription->modify('-3 days');
            $sortie->setDateLimiteInscription($dateLimiteInscription);

            $sortie->setNbInscriptionsMax($faker->numberBetween(8, 25));
            $sortie->setInfosSortie($faker->paragraph());

            // Assigner un organisateur aléatoire
            $participantId = $faker->numberBetween(0, 29);
            $sortie->setOrganisateur($this->getReference('PARTICIPANT_' . $participantId, Participant::class));

            // Assigner le même campus que l'organisateur
            $sortie->setCampus($sortie->getOrganisateur()->getCampus());

            // Assigner un lieu
            $campusName = $sortie->getCampus()->getNom();
            $lieuRef = $this->getLieuRefForCampus($campusName, $faker);
            $sortie->setLieu($this->getReference($lieuRef, Lieu::class));

            // Définir l'état comme "Créée"
            $sortie->setEtat($this->getReference(EtatFixtures::ETAT_CREEE, Etat::class));

            $manager->persist($sortie);
            $this->addReference('SORTIE_CREEE_' . $i, $sortie);
        }
    }

    private function createCancelledSorties(ObjectManager $manager, $faker, $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $sortie = new Sortie();
            $sortie->setNom('Sortie annulée: ' . $faker->words(3, true));

            // Date dans le futur
            $dateDebut = $faker->dateTimeBetween('+1 week', '+2 months');
            $sortie->setDateHeureDebut($dateDebut);

            $duree = $faker->numberBetween(60, 180);
            $sortie->setDuree($duree);

            // Date d'inscription dans le futur
            $dateLimiteInscription = clone $dateDebut;
            $dateLimiteInscription->modify('-2 days');
            $sortie->setDateLimiteInscription($dateLimiteInscription);

            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 15));
            $sortie->setInfosSortie($faker->paragraph() . "\n\nANNULÉE : " . $faker->sentence());

            // Assigner un organisateur aléatoire
            $participantId = $faker->numberBetween(0, 29);
            $sortie->setOrganisateur($this->getReference('PARTICIPANT_' . $participantId, Participant::class));

            // Assigner le même campus que l'organisateur
            $sortie->setCampus($sortie->getOrganisateur()->getCampus());

            // Assigner un lieu
            $campusName = $sortie->getCampus()->getNom();
            $lieuRef = $this->getLieuRefForCampus($campusName, $faker);
            $sortie->setLieu($this->getReference($lieuRef, Lieu::class));

            // Définir l'état comme "Annulée"
            $sortie->setEtat($this->getReference(EtatFixtures::ETAT_ANNULEE, Etat::class));

            $manager->persist($sortie);
            $this->addReference('SORTIE_ANNULEE_' . $i, $sortie);
        }
    }

    private function getLieuRefForCampus(string $campusName, $faker): string
    {
        switch (true) {
            case str_contains($campusName, 'Nantes'):
                return 'LIEU_NANTES_' . $faker->numberBetween(0, 4);
            case str_contains($campusName, 'Rennes'):
                return 'LIEU_RENNES_' . $faker->numberBetween(0, 3);
            case str_contains($campusName, 'Niort'):
                return 'LIEU_NIORT_' . $faker->numberBetween(0, 2);
            case str_contains($campusName, 'Quimper'):
                return 'LIEU_QUIMPER_' . $faker->numberBetween(0, 2);
            default:
                // Utiliser un lieu aléatoire parmi les lieux supplémentaires
                $i = $faker->numberBetween(0, 7);
                $j = $faker->numberBetween(0, 1);
                return 'LIEU_ADDITIONAL_' . $i . '_' . $j;
        }
    }

    public function getDependencies(): array
    {
        return [
            ParticipantFixtures::class,
            LieuFixtures::class,
            EtatFixtures::class,
        ];
    }
}