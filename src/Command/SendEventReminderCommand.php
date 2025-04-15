<?php

namespace App\Command;

use App\Entity\Sortie;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-event-reminders',
    description: 'Envoie des rappels aux participants 48h avant une sortie',
)]
class SendEventReminderCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private EmailService $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Calculer la date à laquelle les sorties commencent dans 48h
        $now = new \DateTime();
        $target = new \DateTime();
        $target->modify('+48 hours');

        // Début et fin de la période de 48h
        $start = clone $target;
        $start->setTime(0, 0, 0);
        $end = clone $target;
        $end->setTime(23, 59, 59);

        $io->info('Recherche des sorties qui commencent le ' . $start->format('d/m/Y'));

        // Récupérer les sorties qui commencent dans 48h
        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('s.dateHeureDebut BETWEEN :start AND :end')
            ->andWhere('e.id != :etatAnnuleId')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('etatAnnuleId', 6)
            ->getQuery()
            ->getResult();

        if(empty($sorties)) {
            $io->info('Aucune sortie trouvée pour cette période');
            return Command::SUCCESS;
        }

        $io->info(sprintf('%d sorties trouvées', count($sorties)));

        $totalEmails = 0;

        foreach ($sorties as $sortie) {
            $io->text('Traitement de la sortie ' . $sortie->getNom());

            $participants = $sortie->getParticipants();
            $io->text(sprintf('%d participants inscrits', count($participants)));

            foreach ($participants as $participant) {
                $this->emailService->sendEventReminder($sortie, $participant);
                $totalEmails++;

                // Petite pause pour ne pas surcharger le serveur mail
                usleep(200000); // 0.2 seconde
            }
        }

        return Command::SUCCESS;
    }
}