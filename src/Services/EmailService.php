<?php

namespace App\Services;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function notifyOrganisateurOfAdminCancellation(Sortie $sortie): void
    {
        $organisateur = $sortie->getOrganisateur();

        // Vérifier que l'organisateur existe
        if (!$organisateur) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($organisateur->getEmail())
            ->subject('Annulation de la sortie : '.$sortie->getNom().' a été annulée par un administrateur')
            ->htmlTemplate('emails/annulation_admin_notification.html.twig')
            ->context([
                'organisateur'=>$organisateur,
                'sortie'=>$sortie,
                'lien_accueil' => $this->urlGenerator->generate('app_main', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
    }
    public function sendInscriptionConfirmation(Sortie $sortie, Participant $participant): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($participant->getEmail())
            ->subject("Confirmation d'inscription : " . $sortie->getNom())
            ->htmlTemplate('emails/inscription_confirmation.html.twig')
            ->context([
                'participant' => $participant,
                'sortie' => $sortie,
                'lien_sortie' => $this->urlGenerator->generate('sortie_detail', ['id' => $sortie->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

        $this->mailer->send($email);
    }

    public function sendDesistementConfirmation(Sortie $sortie, Participant $participant): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($participant->getEmail())
            ->subject("Confirmation de désistement : " . $sortie->getNom())
            ->htmlTemplate('emails/desistement_confirmation.html.twig')
            ->context([
                'participant' => $participant,
                'sortie' => $sortie,
                'lien_sortie' => $this->urlGenerator->generate('sortie_index', ['id' => $sortie->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        $this->mailer->send($email);
    }
}