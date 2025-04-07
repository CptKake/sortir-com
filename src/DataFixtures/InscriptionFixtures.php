<?php

namespace App\DataFixtures;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 1. Inscriptions aux sorties passées
        $this->createInscriptionsForSorties($manager, $faker, 'SORTIE_PASSEE_', 15, 3, 15, true);

        // 2. Inscriptions aux sorties actives/ouvertes
        $this->createInscriptionsForSorties($manager, $faker, 'SORTIE_ACTIVE_', 10, 1, 8, false);

        // 3. Inscriptions aux sorties clôturées
        $this->createInscriptionsForSorties($manager, $faker, 'SORTIE_CLOTUREE_', 5, 3, 10, false);

        // 4. Pas d'inscriptions aux sorties en création (état "Créée")

        // 5. Inscriptions aux sorties annulées (quelques inscriptions avant annulation)
        $this->createInscriptionsForSorties($manager, $faker, 'SORTIE_ANNULEE_', 5, 1, 5, false);

        $manager->flush();
    }

    private function createInscriptionsForSorties(ObjectManager $manager, $faker, $sortieRefPrefix, $sortieCount, $minInscriptions, $maxInscriptions, $includeOrganisateur): void
    {
        for ($i = 0; $i < $sortieCount; $i++) {
            $sortieRef = $sortieRefPrefix . $i;

            if (!$this->hasReference($sortieRef, Sortie::class)) {
                continue;
            }

            $sortie = $this->getReference($sortieRef, Sortie::class);
            $organisateur = $sortie->getOrganisateur();
            $campus = $sortie->getCampus();

            // Déterminer le nombre d'inscriptions pour cette sortie
            $nbInscriptions = min(
                $faker->numberBetween($minInscriptions, $maxInscriptions),
                $sortie->getNbInscriptionsMax()
            );

            // Ensemble pour suivre les participants déjà inscrits
            $inscrits = [];

            // Si l'organisateur doit être inclus dans les inscrits
            if ($includeOrganisateur && $nbInscriptions > 0) {
                $inscription = new Inscription();
                $inscription->setParticipant($organisateur);
                $inscription->setSortie($sortie);
                // Utiliser l'approche la plus sûre : partir de la date actuelle
                if ($sortie->getDateLimiteInscription() > new \DateTime()) {
                    // Si la date limite est dans le futur, utiliser une période entre il y a 10 jours et aujourd'hui
                    $dateDebut = new \DateTime('-10 days');
                    $dateFin = new \DateTime();
                } else {
                    // Si la date limite est dans le passé, utiliser une période entre un mois avant et la date limite
                    $dateDebut = clone $sortie->getDateLimiteInscription();
                    $dateDebut->modify('-1 month');
                    $dateFin = clone $sortie->getDateLimiteInscription();
                }

// Vérifier que la date de début est bien antérieure à la date de fin
                if ($dateDebut > $dateFin) {
                    // Inverser les dates si nécessaire
                    $temp = $dateDebut;
                    $dateDebut = $dateFin;
                    $dateFin = $temp;
                }

                $inscription->setDateInscription($faker->dateTimeBetween($dateDebut, $dateFin));

                $manager->persist($inscription);
                $inscrits[] = $organisateur->getId();
                $nbInscriptions--;
            }

            // Ajouter des inscriptions aléatoires
            $tentatives = 0;
            while (count($inscrits) < $nbInscriptions + ($includeOrganisateur ? 1 : 0) && $tentatives < 50) {
                $tentatives++;

                // Préférer les participants du même campus
                $memeCampus = $faker->boolean(70);

                if ($memeCampus) {
                    // Trouver des participants du même campus que la sortie
                    $possibleParticipants = [];
                    for ($j = 0; $j < 30; $j++) {
                        $participant = $this->getReference('PARTICIPANT_' . $j, Participant::class);
                        if ($participant->getCampus() === $campus && $participant !== $organisateur && !in_array($participant->getId(), $inscrits)) {
                            $possibleParticipants[] = $participant;
                        }
                    }

                    if (!empty($possibleParticipants)) {
                        $participant = $faker->randomElement($possibleParticipants);
                    } else {
                        continue;
                    }
                } else {
                    // Participant aléatoire
                    $participantId = $faker->numberBetween(0, 29);
                    $participant = $this->getReference('PARTICIPANT_' . $participantId, Participant::class);

                    // Éviter d'inscrire l'organisateur ou un participant déjà inscrit
                    if ($participant === $organisateur || in_array($participant->getId(), $inscrits)) {
                        continue;
                    }
                }

                // Créer l'inscription
                $inscription = new Inscription();
                $inscription->setParticipant($participant);
                $inscription->setSortie($sortie);

                $inscription->setDateInscription(new \DateTime('-1 month'));

                $manager->persist($inscription);
                $inscrits[] = $participant->getId();
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            SortieFixtures::class,
            ParticipantFixtures::class,
        ];
    }
}