<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie/{id}', name: 'sortie_', requirements: ['id' => '\d+'])]
final class InscriptionController extends AbstractController
{

	#[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
	public function inscription(Sortie $sortie, EntityManagerInterface $em): Response
	{
		if (!$sortie) {
			throw $this->createNotFoundException('Sortie inconnue');
		}

		$inscription = new Inscription();
		$valide = true;

		// recuperer l'user
		$user = $this->getUser();

		// Vérifications
		foreach ($sortie->getInscriptions() as $inscription) {
			if ($inscription->getParticipant() === $user) {
				$this->addFlash('error', 'Vous êtes déjà inscrit !');
				$valide = false;
			}
		}

		if ($sortie->getEtat()->getId() === 6) {
			$this->addFlash('error', 'La sortie a été annulée par l\'organisateur !');
			$valide = false;
		} elseif ($sortie->getEtat()->getId() === 3) {
			$this->addFlash('error', 'La sortie a été cloturée par l\'organisateur !');
			$valide = false;
		} elseif ($sortie->getEtat()->getId() === 5) {
			$this->addFlash('error', 'La sortie est déjà passée !');
			$valide = false;
		}

		if (!$valide) {
			return new RedirectResponse($this->generateUrl('sortie_detail', ['id' => $sortie->getId()]));
		}

		// créer inscription
		$inscription->setSortie($sortie);
		$inscription->setParticipant($user);
		$inscription->setDateInscription(new \DateTime('now'));

		// ajouter Inscription à Sortie
		$sortie->addInscription($inscription);
		$sortie->addParticipant($user);

		// persister
		$em->persist($inscription);
		$em->persist($sortie);
		$em->flush();

		// add flash
		$this->addFlash('success', 'Vous êtes inscrits à la sortie : ' . $sortie->getNom() );

		return new RedirectResponse($this->generateUrl('sortie_detail', ['id' => $sortie->getId()]));
	}



}
