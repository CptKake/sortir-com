<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findUpcomingSorties();

        $campusRepository = $entityManager->getRepository(Campus::class);
        $campus = $campusRepository->findAll();

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'sorties' => $sorties,
            'campus' => $campus,
        ]);
    }

    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        $email = (new \Symfony\Component\Mime\Email())
            ->from('noreply@sortir.com')
            ->to('test@demo.local')
            ->subject('Test direct Symfony')
            ->text('Ceci est un test de mail envoyé manuellement');

        $mailer->send($email);

        return new Response('✅ Mail envoyé');
    }



}
