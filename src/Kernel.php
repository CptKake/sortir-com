<?php

namespace App;

use App\Command\SendEventReminderCommand;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

class Kernel extends BaseKernel implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return new Schedule();
    }

    use MicroKernelTrait;

    #[AsSchedule]
    public function schedule(Schedule $schedule): Schedule
    {
        // Planifier l'exécution de la commande tous les jours à minuit
        $schedule->add(
            RecurringMessage::cron('0 0 * * *', new \App\Message\SendEventReminderMessage())
        );

        return $schedule;
    }

}