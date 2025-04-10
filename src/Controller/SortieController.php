<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Services\EmailService;
use App\Services\MapService;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{
	private $mapService;

	public function __construct(MapService $mapService){
		$this->mapService = $mapService;
	}

	#[Route('', name: 'index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

	#[Route('/create', name: 'create', methods: ['GET', 'POST'])]
	public function create(Request $request, EntityManagerInterface $em): Response
	{
		$sortie = new Sortie();
		$user = $this->getUser();
		$sortie->setOrganisateur($user);
		$form = $this->createForm(SortieType::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($sortie);
			$em->flush();

			$this->addFlash('success', 'La sortie a été créée');

			return $this->redirectToRoute('sortie_index');
		}

		return $this->render('sortie/create.html.twig', [
			'sortie' => $sortie,
			'form' => $form,
		]);

	}

	#[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
	public function show(Sortie $sortie, EntityManagerInterface $em): Response
	{
		if (!$sortie) {
			throw $this->createNotFoundException('Sortie inconnue');
		}

		$lieu = $em->getRepository(Lieu::class)->findOneBy(array('id' => $sortie->getLieu()->getId()));
		$mapScript = $this->mapService->generateMapScript($lieu->getLatitude(), $lieu->getLongitude(), $lieu->getNom());

		return $this->render('sortie/detail.html.twig', [
			'sortie' => $sortie,
			'lieu' => $lieu,
			'mapScript' => $mapScript,
		]);
	}

	#[Route('/{id}/editer', name: 'editer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
	public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
	{
		$form = $this->createForm(SortieType::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();

			$this->addFlash('success', 'La sortie a été modifiée');

			return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('sortie/edit.html.twig', [
			'sortie' => $sortie,
			'form' => $form,
		]);
	}

	#[Route('/{id}/annuler', name: 'annuler',requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
	public function annuler(
        Request $request,
        Sortie $sortie,
        EntityManagerInterface $em,
        EmailService $emailService
    ): Response
	{
        // Vérifier que l'utilisateur est l'organisateur
        $currentUser = $this->getUser();
        if (!$currentUser || $sortie->getOrganisateur() !== $currentUser) {
            $this->addFlash('error', "Vous n'êtes pas autorisé à annuler cette sortie.");
            return $this->redirectToRoute('sortie_detail', ['id'=> $sortie->getId()]);
        }

        // Vérifier que la sortie n'est pas déjà annulée ou passée ou en cours
        $etatAnnulee = $em->getRepository(Etat::class)->find(6);
        $etatPassee = $em->getRepository(Etat::class)->find(5);
        $etatEnCours = $em->getRepository(Etat::class)->find(4);

        if($sortie->getEtat() === $etatAnnulee) {
            $this->addFlash('error', "Cette sortie est déjà annulée.");
            return $this->redirectToRoute('sortie_detail', ['id'=> $sortie->getId()]);
        }
        if($sortie->getEtat() === $etatPassee) {
            $this->addFlash('error', "Impossible d'annuler une sortie passée.");
            return $this->redirectToRoute('sortie_detail', ['id'=> $sortie->getId()]);
        }
        if($sortie->getEtat() === $etatEnCours) {
            $this->addFlash('error', "Impossible d'annuler une sortie en cours.");
            return $this->redirectToRoute('sortie_detail', ['id'=> $sortie->getId()]);
        }

        // Créer le formulaire pour le motif d'annulation
        $form = $this->createForm(AnnulationType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Récupérer le motif d'annulation
            $data = $form->getData();
            $motifAnnulation = $data['motifAnnulation'];

            //Sauvegarder les participants avant de les détacher pour l'email
            $participants = clone $sortie->getParticipants();

            // Changer l'état en 'Annulée'
            $sortie->setEtat($etatAnnulee);
            $sortie->setMotifAnnulation($motifAnnulation);

            // Envoyer les emails aux participants
            $emailService->sendAnnulationEmails($sortie);

            // Supprimer les inscriptions (ne pas supprimer les participants eux-mêmes!)
            foreach ($participants as $participant) {
                $sortie->removeParticipant($participant);
            }

            // Valider les modifications
            $em->flush();

            $this->addFlash('success', "La sortie a été annulée. Les participants ont été notifiés par email.");

            return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        // Afficher le formulaire
        return $this->render('sortie/annuler_sortie.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
	}

	#[Route('/{id}/delete', name: 'supprimer',requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
	public function delete(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
	{
		$em->remove($sortie);
		$em->flush();

		$this->addFlash('success', 'La sortie a été supprimée');

		return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
	}

}
