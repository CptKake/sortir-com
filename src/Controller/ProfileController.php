<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Form\PasswordUpdateType;
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
    #[Route('/', name: 'app_profile_show')]
    public function show(): Response
    {
        // Récupère l'utilisateur connecté
        $participant = $this->getUser();

        if (!$participant) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de photo
            $photoFile = $form->get('urlPhoto')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );

                    // Met à jour le chemin de la photo
                    $participant->setUrlPhoto($newFilename);
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

    #[Route('/password', name: 'app_profile_password')]
    public function updatePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
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