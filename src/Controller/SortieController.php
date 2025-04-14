<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SortieFilterType;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Services\AddressAutocompleteService;
use App\Services\EmailService;
use App\Services\MapService;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{
	private $mapService;

	public function __construct(MapService $mapService){
		$this->mapService = $mapService;
	}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(SortieFilterType::class);
        $form->handleRequest($request);

        $user = $this->getUser();

        $qb = $sortieRepository->createQueryBuilder('s')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('s.inscriptions', 'i')
            ->leftJoin('s.campus', 'c')
            ->leftJoin('s.etat', 'e')
            ->addSelect('o', 'i', 'c', 'e');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filtres communs
            if ($data['campus']) {
                $qb->andWhere('s.campus = :campus')
                    ->setParameter('campus', $data['campus']);
            }

            if ($data['search']) {
                $qb->andWhere('s.nom LIKE :search')
                    ->setParameter('search', '%' . $data['search'] . '%');
            }

            if ($data['dateDebut']) {
                $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                    ->setParameter('dateDebut', $data['dateDebut']);
            }

            if ($data['dateFin']) {
                $qb->andWhere('s.dateHeureDebut <= :dateFin')
                    ->setParameter('dateFin', $data['dateFin']);
            }

            // Logique conditionnelle :
            if (!empty($data['passees'])) {
                // 🎯 Si sorties passées cochées

                if (!empty($data['organisateur'])) {
                    $qb->andWhere('s.organisateur = :user')
                        ->andWhere('e.libelle = :etatPassee')
                        ->setParameter('user', $user)
                        ->setParameter('etatPassee', 'Passée');
                } else {
                    $qb->andWhere('e.libelle = :etatPassee')
                        ->setParameter('etatPassee', 'Passée');
                }

            } else {
                // 🎯 Si "Sorties passées" NON cochées

                if (!empty($data['organisateur'])) {
                    $qb->andWhere('s.organisateur = :user')
                        ->setParameter('user', $user);
                } else {
                    $qb->andWhere('e.libelle = :etatOuverte')
                        ->andWhere('s.dateHeureDebut > :now')
                        ->setParameter('etatOuverte', 'Ouverte')
                        ->setParameter('now', new \DateTime());
                }
            }

            // Autres filtres complémentaires
            if ($data['inscrit']) {
                $qb->andWhere(':user MEMBER OF s.inscriptions')
                    ->setParameter('user', $user);
            }

            if ($data['nonInscrit']) {
                $qb->andWhere(':user NOT MEMBER OF s.inscriptions')
                    ->setParameter('user', $user);
            }

        } else {
            // Cas par défaut : uniquement les sorties ouvertes à venir OU mes sorties
            $qb->andWhere('(e.libelle = :etatOuverte AND s.dateHeureDebut > :now) OR s.organisateur = :user')
                ->setParameter('etatOuverte', 'Ouverte')
                ->setParameter('now', new \DateTime())
                ->setParameter('user', $user);
        }

        $sorties = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'form' => $form->createView(),
        ]);
    }





    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
	public function create(Request $request, EntityManagerInterface $em, AddressAutocompleteService $addressService): Response
	{
		$sortie = new Sortie();
		$user = $this->getUser();
		$sortie->setOrganisateur($user);
		$sortie->setEtat($em->getRepository(Etat::class)->find(1));
		$form = $this->createForm(SortieType::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($sortie);
			$em->flush();

			$this->addFlash('success', 'La sortie a été créée');

			return $this->redirectToRoute('sortie_index');
		}

        $addressScript = $addressService->generateAutocompleteScript('.adresse-autocomplete');

		return $this->render('sortie/create.html.twig', [
			'sortie' => $sortie,
			'form' => $form,
            'address_script' => $addressScript,
		]);

	}

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie, EntityManagerInterface $em): Response
    {
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie inconnue');
        }

	    $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(array('libelle' => $sortie->getEtat()->getLibelle())));

        $lieu = $em->getRepository(Lieu::class)->findOneBy(array('id' => $sortie->getLieu()->getId()));
        $mapScript = $this->mapService->generateMapScript($lieu->getLatitude(), $lieu->getLongitude(), $lieu->getNom());
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu,
            'mapScript' => $mapScript,
            ]);
    }

	#[Route('/{id}/editer', name: 'editer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
	public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em, AddressAutocompleteService $addressService): Response
	{
		$form = $this->createForm(SortieType::class, $sortie);
		$form->handleRequest($request);

		$sortie->setEtat($em->getRepository(Etat::class)->findOneBy(array('libelle' => $sortie->getEtat()->getLibelle())));

		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();

			$this->addFlash('success', 'La sortie a été modifiée');

			return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
		}

        $addressScript = $addressService->generateAutocompleteScript('.adresse-autocomplete');

		return $this->render('sortie/edit.html.twig', [
			'sortie' => $sortie,
			'form' => $form,
            'address_script' => $addressScript,
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
        // Vérifier que l'utilisateur est l'organisateur OU un administrateur
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles());
        $isOrganisateur = $currentUser && $currentUser === $sortie->getOrganisateur();

        if (!$currentUser || (!$isAdmin && !$isOrganisateur)) {
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

            //Ajouter un préfixe au motif si c'est un admin qui annule
            if($isAdmin && !$isOrganisateur) {
                $sortie->setMotifAnnulation("Annulation de la sortie par un administrateur : " . $motifAnnulation);

                // Notifier l'organisateur de l'annulation par un admin
                $emailService->notifyOrganisateurOfAdminCancellation($sortie);
            } else {
                $sortie->setMotifAnnulation($motifAnnulation);
            }

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
            'isAdmin' => $isAdmin,
            'isOrganisateur' => $isOrganisateur,
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

	#[Route('/{id}/publier', name: 'publier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
	public function publish(Sortie $sortie, EntityManagerInterface $em): Response
	{

		$sortie->setEtat($em->getRepository(Etat::class)->findOneBy(array('libelle' => $sortie->getEtat()->getLibelle())));

		if ($sortie->getEtat()->getId() === 1) {
			$sortie->setEtat($em->getRepository(Etat::class)->find(2));
			$em->flush();
			$this->addFlash('success', 'La sortie ' . $sortie->getNom() . ' a été publiée');
		} else {
			$this->addFlash('error', 'Erreur lors de la publication de la sortie ' . $sortie->getNom());
			return $this->redirectToRoute('sortie_detail', ['id'=> $sortie->getId()]);
		}

		return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
	}

}
