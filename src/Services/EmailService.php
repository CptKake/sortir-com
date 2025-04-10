<?php

namespace App\Services;

use App\Entity\Sortie;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private string $senderEmail;

    public function __construct(
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        string $senderEmail = 'no-reply@sortir.com'
    ) {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->senderEmail = $senderEmail;
    }

    public function sendAnnulationEmails(Sortie $sortie): void
    {
        $participants = $sortie->getParticipants();

        foreach ($participants as $participant) {
            $email = (new TemplatedEmail())
                ->from($this->senderEmail)
                ->to($participant->getEmail())
                ->subject('Annulation de la sortie : '.$sortie->getNom())
                ->htmlTemplate('emails/annulation.html.twig')
                ->context([
                    'participant'=>$participant,
                    'sortie'=>$sortie,
                    'lien_accueil' => $this->urlGenerator->generate('app_main', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);
            $this->mailer->send($email);
        }
    }
}