<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new Participant();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un utilisateur avec le même pseudo ou email existe déjà
            $existingUser = $entityManager->getRepository(Participant::class)->findOneBy(['pseudo' => $user->getPseudo()]);
            if ($existingUser) {
                $this->addFlash('error', 'Il existe déjà un compte avec ce pseudo.');
                return $this->redirectToRoute('app_register');
            }

            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setMotDePasse($hashedPassword);

            // Rôle par défaut
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);

            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger vers login après inscription
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
