<?php

	namespace App\EventListener;

	use App\Entity\Etat;
	use App\Entity\Sortie;
	use Doctrine\Persistence\Event\LifecycleEventArgs;

	class StatutSortieListener
	{

		public function postLoad(LifecycleEventArgs $args) {

			$sortie = $args->getObject();

			if (!$sortie instanceof Sortie) {
				return null;
			}

			$etat = $sortie->getEtat()->getId();
			$statut = 'Créée';
			$dateNow = new \DateTime("now");
			$dateFin = clone $sortie->getDateHeureDebut();
			$datefin = $dateFin->add(new \DateInterval('PT' . $sortie->getDuree() . 'M'));

			// Attribut selon l'état de la sortie
			if ($etat === 2) {
				$statut = 'Ouverte';
			}

			if ($sortie->getDateLimiteInscription() < $dateNow) {
				$statut = 'Clôturée';
			}

			if ($sortie->getDateHeureDebut() < $dateNow and $dateFin > $dateNow ) {
				$statut = 'Activité en cours';
			}

			// Attribut si sortie complète
			if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
				$statut = 'Complète';
			}

			if ($sortie->getDateHeureDebut() < $dateNow and $dateFin < $dateNow) {
				$statut = 'Passée';
			}

			if ($etat === 6) {
				$statut = 'Annulée';
			}

			$newEtat = new Etat();
			$newEtat->setLibelle($statut);
			$sortie->setEtat($newEtat);

		}

	}
