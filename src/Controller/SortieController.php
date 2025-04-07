<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{
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
		$form = $this->createForm(SortieType::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($sortie);
			$em->flush();

			return $this->redirectToRoute('sortie_index');
		}

		return $this->render('sortie/create.html.twig', [
			'sortie' => $sortie,
			'form' => $form,
		]);

	}

	#[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
	public function show(Sortie $sortie): Response
	{
		if (!$sortie) {
			throw $this->createNotFoundException('Sortie inconnue');
		}
		return $this->render('sortie/detail.html.twig', [
			'sortie' => $sortie,
		]);
	}

	#[Route('/{id}/editer', name: 'editer', methods: ['GET', 'POST'])]
	public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
	{
		$form = $this->createForm(SortieType::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();

			return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
		}

		return $this->render('sortie/edit.html.twig', [
			'sortie' => $sortie,
			'form' => $form,
		]);
	}

	#[Route('/{id}', name: 'supprimer', methods: ['POST'])]
	public function delete(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
	{
		if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->getPayload()->getString('_token'))) {
			$em->remove($sortie);
			$em->flush();
		}

		return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
	}

}
