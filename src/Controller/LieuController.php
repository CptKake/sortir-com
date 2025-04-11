<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Services\AddressAutocompleteService;
use App\Services\MapService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/lieu')]
final class LieuController extends AbstractController{

    private MapService $mapService;

    public function __construct(MapService $mapService){
        $this->mapService = $mapService;
    }

    #[IsGranted("ROLE_USER")]
    #[Route(name: 'app_lieu_index', methods: ['GET'])]
    public function index(
        LieuRepository $lieuRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $form = $this->createForm(\App\Form\LieuFilterType::class, null, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        $qb = $lieuRepository->createQueryBuilder('s');

        $search = $form->get('search')->getData();
        if (!empty($search)) {
            $qb->andWhere('s.nom LIKE :search OR s.ville LIKE :search OR s.codePostal LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $lieux = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('lieu/index.html.twig', [
            'lieux' => $lieux,
            'form' => $form->createView(),
        ]);
    }



    #[IsGranted("ROLE_USER")]
    #[Route('/new', name: 'app_lieu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AddressAutocompleteService $addressService): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', '');

            return $this->redirectToRoute('app_lieu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/new.html.twig', [
            'lieu' => $lieu,
            'form' => $form,
            'address_script' => $addressService->generateAutocompleteScript('.adresse-autocomplete'), [
                'limit'=>8,
                'minLength'=>3,
            ]
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/{id}', name: 'app_lieu_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Lieu $lieu): Response
    {
        $mapScript = $this->mapService->generateMapScript($lieu->getLatitude(), $lieu->getLongitude(), $lieu->getNom());

        return $this->render('lieu/show.html.twig', [
            'lieu' => $lieu,
            'mapScript' => $mapScript,
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/{id}/edit', name: 'app_lieu_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Lieu $lieu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lieu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/edit.html.twig', [
            'lieu' => $lieu,
            'form' => $form,
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/{id}', name: 'app_lieu_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Lieu $lieu, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lieu->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lieu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lieu_index', [], Response::HTTP_SEE_OTHER);
    }
}
