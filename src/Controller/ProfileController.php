<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Form\PasswordUpdateType;
use App\Services\ImageService;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    #[Route('/', name: 'app_profile_show')]
    public function show(): Response
    {
        // Récupère l'utilisateur connecté
        $participant = $this->getUser();

        if (!$participant) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre profil.');
            return $this->redirectToRoute('app_login');
        }

        // On s'assure que l'image par défaut existe
        $this->imageService->ensureDefaultImageExists();

        return $this->render('profile/show.html.twig', [
            'participant' => $participant,
            'isOwnProfile' => true,
        ]);
    }

    #[Route('/{id}', name: 'app_profile_detail', requirements: ['id' => '\d+'])]
    public function showById(
        ParticipantRepository $participantRepository,
        int $id
    ): Response
    {
        // Vérifier si l'utilisateur est connectée
        $currentUser = $this->getUser();
        if (!$currentUser) {
            $this->addFlash('error', 'Vous devez être connecté pour consulter les profils.');
            return $this->redirectToRoute('app_login');
        }

        // Si l'ID correspond à l'utilisateur connecté, rediriger vers son propre profil
        if ($id === $currentUser->getId()) {
            return $this->redirectToRoute('app_profile_show');
        }

        // Récupérer le participant demandé
        $participant = $participantRepository->find($id);

        // Si le participant n'existe pas, afficher message d'erreur
        if (!$participant) {
            $this->addFlash('error', 'Ce participant n\'existe pas ou a été supprimé.');
            return $this->redirectToRoute('app_main');
        }

        // On s'assure que l'image par défaut existe
        $this->imageService->ensureDefaultImageExists();

        return $this->render('profile/show.html.twig', [
            'participant'=>$participant,
            'isOwnProfile' => false,
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageService $imageService
    ): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier votre profil.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de photo
            $photoFile = $form->get('urlPhoto')->getData();

            if ($photoFile) {
                try {
                    $imageService->uploadImage($participant, $photoFile);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de votre photo.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');

            return $this->redirectToRoute('app_profile_show');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'participant' => $participant,
        ]);
    }

    #[Route('/delete-photo', name: 'app_profile_delete_photo', methods: ['POST'])]
    public function deletePhoto(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $participant = $this->getUser();

        if(!$participant) {
            $this->addFlash('error', 'Vous devez être connecté pour effectuer cette action.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'utilisateur a une photo et qu'elle n'est pas déjà celle par défaut
        if (!$participant->getUrlPhoto() || $participant->getUrlPhoto() === 'défaut.png') {
            $this->addFlash('info', 'Vous utilisez déjà l\'image par défaut.');
            return $this->redirectToRoute('app_profile_show');
        }

        // Protection contre CSRF
        if($this->isCsrfTokenValid('delete-photo', $request->request->get('_token'))) {
            // Appel au service pour supprimer physiquement le fichier et mettre à jour l'entité
            $this->imageService->removeImage($participant);

            // Enregistrer les modifications
            $entityManager->flush();

            $this->addFlash('success', 'Votre photo de profil a été supprimée');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de votre photo.');
        }

        return $this->redirectToRoute('app_profile_show');
    }

    #[Route('/password', name: 'app_profile_password')]
    public function updatePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier votre mot de passe.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(PasswordUpdateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérifie l'ancien mot de passe
            if (!$passwordHasher->isPasswordValid($participant, $data['oldPassword'])) {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
                return $this->redirectToRoute('app_profile_password');
            }

            // Hache le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $participant,
                $data['newPassword']
            );

            $participant->setMotDePasse($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès !');

            return $this->redirectToRoute('app_profile_show');
        }

        return $this->render('profile/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}