<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Campus;
use App\Form\AdminUserType;
use App\Form\ImportCsvType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    #[Route('/admin/import', name: 'admin_import')]
    public function import(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Création du formulaire d'upload CSV
        $form = $this->createForm(\App\Form\ImportCsvType::class);
        $form->handleRequest($request);

        // Tableau pour accumuler les messages d'erreur sur les lignes du CSV
        $importErrors = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csvFile')->getData();

            if ($file) {
                // Ouverture du fichier CSV
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    // Lecture de la première ligne d'en-tête
                    $header = fgetcsv($handle, 0, ';');

                    while (($row = fgetcsv($handle, 0, ';')) !== false) {
                        // On utilise array_combine pour associer les données avec les intitulés
                        if (count($header) !== count($row)) {
                            $importErrors[] = "Ligne invalide, le nombre de colonnes ne correspond pas.";
                            continue;
                        }
                        $data = array_combine($header, $row);

                        // Récupérer les valeurs attendues depuis le CSV
                        $nom       = trim($data['nom'] ?? '');
                        $prenom    = trim($data['prenom'] ?? '');
                        $email     = trim($data['email'] ?? '');
                        $pseudo    = trim($data['pseudo'] ?? '');
                        $telephone = trim($data['telephone'] ?? '');
                        $campusName= trim($data['campus'] ?? '');
                        $plainPwd  = trim($data['motDePasse'] ?? ''); // mot de passe en clair

                        // Vérifier que les champs obligatoires sont présents (vous pouvez ajouter plus de validations ici)
                        if (!$email || !$pseudo) {
                            $importErrors[] = "Email ou pseudo manquant pour la ligne avec le nom '{$nom}'.";
                            continue;
                        }


                        $existingByEmail = $entityManager->getRepository(Participant::class)->findOneBy(['email' => $email]);
                        $existingByPseudo = $entityManager->getRepository(Participant::class)->findOneBy(['pseudo' => $pseudo]);

                        if ($existingByEmail || $existingByPseudo) {
                            $importErrors[] = "Utilisateur avec l'email '$email' ou le pseudo '$pseudo' existe déjà.";
                            continue;
                        }

                        // Création du Participant
                        $participant = new Participant();
                        $participant->setNom($nom);
                        $participant->setPrenom($prenom);
                        $participant->setEmail($email);
                        $participant->setPseudo($pseudo);
                        $participant->setTelephone($telephone);
                        $participant->setActif(true);
                        $participant->setRoles(['ROLE_USER']);

                        // Gestion du Campus
                        $campus = $entityManager->getRepository(Campus::class)->findOneBy(['nom' => $campusName]);
                        if (!$campus) {

                            $campus = new Campus();
                            $campus->setNom($campusName);
                            $entityManager->persist($campus);

                        }
                        $participant->setCampus($campus);

                        if (!$plainPwd) {
                            $plainPwd = 'DefaultPassword123!';
                        }
                        $hashedPassword = $passwordHasher->hashPassword($participant, $plainPwd);
                        $participant->setPassword($hashedPassword);

                        $entityManager->persist($participant);
                    }
                    fclose($handle);

                    // Sauvegarde en base uniquement si aucune erreur critique n'est survenue sur l'import
                    $entityManager->flush();
                } else {
                    $this->addFlash('error', 'Impossible d\'ouvrir le fichier CSV.');
                }

                // Ajouter les erreurs d'import dans les messages flash
                foreach ($importErrors as $error) {
                    $this->addFlash('error', $error);
                }

                if (empty($importErrors)) {
                    $this->addFlash('success', 'Importation terminée avec succès !');
                }

                return $this->redirectToRoute('admin_import');
            }
        }

        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
            'errors' => $importErrors,
        ]);
    }

    #[Route('/admin/utilisateur/ajouter', name: 'admin_user_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // Sécurise la route

        $participant = new Participant();
        $form = $this->createForm(AdminUserType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $hashedPassword = $hasher->hashPassword(
                $participant,
                $form->get('motDePasse')->getData()
            );
            $participant->setMotDePasse($hashedPassword);
            $participant->setRoles(['ROLE_USER']); // ou ajouter ROLE_ADMIN selon checkbox
            $participant->setActif(true);

            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('admin_user_add');
        }

        return $this->render('admin/add_user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/utilisateurs', name: 'admin_user_list')]
    public function userList(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $query = $em->getRepository(Participant::class)->createQueryBuilder('u')->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/user_list.html.twig', [
            'utilisateurs' => $pagination,
        ]);
    }

    #[Route('/admin/utilisateurs/action', name: 'admin_users_mass_action', methods: ['POST'])]
    public function massAction(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $ids = $request->request->all('users');
        $action = $request->request->get('action');

        if (empty($ids)) {
            $this->addFlash('error', 'Aucun utilisateur sélectionné.');
            return $this->redirectToRoute('admin_user_list');
        }

        $modifications = 0;
        $skipped = 0;

        foreach ($ids as $id) {
            $user = $em->getRepository(Participant::class)->find($id);
            if (!$user) continue;

            if ($action === 'delete') {
                foreach ($user->getInscriptions() as $inscription) {
                    $em->remove($inscription);
                }
                $em->remove($user);
                $modifications++;

            } elseif ($action === 'activate') {
                if (!$user->isActif()) {
                    $user->setActif(true);
                    $modifications++;
                } else {
                    $skipped++;
                }

            } elseif ($action === 'deactivate') {
                if ($user->isActif()) {
                    $user->setActif(false);
                    $modifications++;
                } else {
                    $skipped++;
                }
            }
        }

        $em->flush();

        if ($modifications > 0) {
            $this->addFlash('success', "Action effectuée avec succès.");
        }
        if ($skipped > 0) {
            $this->addFlash('error', "$skipped utilisateur(s) étaient déjà dans l'état souhaité.");
        }

        return $this->redirectToRoute('admin_user_list');
    }



    #[Route('/{id}', name: 'app_profile_detail',requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showById(    ParticipantRepository $participantRepository,   $id): Response
    {
        $participant = $participantRepository->find($id);
        return $this->render('profile/show.html.twig', [
            'participant'=>$participant
        ]
        );
    }


}
