<?php

namespace App\Command;

use App\Service\DeclarationService;
use App\Service\InvoiceService;
use App\Service\ReportService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name       : 'app:notifications',
    description: 'Envoi les notifications par email',
    aliases    : []
)]
class NotificationCommand extends Command
{
    public function __construct(
        protected ParameterBagInterface $params,
        protected MailerInterface       $mailer,
        protected Environment           $templating,
        protected TranslatorInterface   $translator,
        protected DeclarationService    $declarationService,
        protected InvoiceService        $invoiceService,
        protected ReportService         $reportService,
    ) {
        parent::__construct();
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $notifications = array_merge(
            $this->invoiceService->getNotifications(),
            $this->declarationService->getNotifications(),
            $this->reportService->getNotifications(),
        );

        if (count($notifications) > 0) {
            $email = (new Email())
                ->from('Jaden Achain <notifications@jaden-achain.dev>')
                ->to($this->params->get('MAILER_FROM'))
                ->subject(
                    $this->params->get('MAILER_SUBJECT') . ' ' .
                    $this->translator->trans('Notifications')
                )
                ->text(
                    $this->templating->render(
                        'email/notifications.txt.twig',
                        ['notifications' => $notifications]
                    )
                );

            $this->mailer->send($email);

            $io->success('Nouvelles notifications');
            $io->listing($notifications);
        } else {
            $io->success('Aucune nouvelle notification');
        }

        return 0;
    }
}
