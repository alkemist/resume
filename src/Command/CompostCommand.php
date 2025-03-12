<?php

namespace App\Command;

use App\Service\DeclarationService;
use App\Service\InvoiceService;
use App\Service\ReportService;
use DateTimeImmutable;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsCommand(
    name: 'app:compost',
    description: '',
    aliases: []
)]
class CompostCommand extends Command
{
    public function __construct(
        protected ParameterBagInterface $params,
        protected MailerInterface       $mailer,
        protected Environment           $templating,
        protected TranslatorInterface   $translator,
        protected DeclarationService    $declarationService,
        protected InvoiceService        $invoiceService,
        protected ReportService         $reportService,
    )
    {
        parent::__construct();
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url_csv = $this->params->get('COMMAND_COMPOST_URL_CSV');
        $url_link = $this->params->get('COMMAND_COMPOST_URL_LINK');
        $mail_to = $this->params->get('COMMAND_COMPOST_MAIL_TO');
        $diffDaysMax = intval($this->params->get('COMMAND_COMPOST_DIFF_DAYS'));

        try {
            $data = file_get_contents($url_csv);
            $rows = explode("\n", $data);

            $mails_csv = file_get_contents(__DIR__ . '/../../compost_mails.csv');
            $mails = array_filter(explode("\n", $mails_csv), function ($val) {
                return !empty(trim($val));
            });

            $mails_address = array_map(function ($val) {
                return Address::create(trim($val));
            }, $mails);

            $now = new DateTimeImmutable();
            $permanence = null;
            $annulation = false;

            foreach ($rows as $index => $row) {
                if ($index > 0) {
                    $row_csv = array_map(function ($val) {
                        return trim($val);
                    }, str_getcsv($row));

                    $date_str = $row_csv[1];

                    if ($date_str && preg_match('#\d{1,2}/\d{1,2}/\d{2,4}#', $date_str)) {
                        $date_arr = explode('/', $date_str);
                        $date = new DateTimeImmutable($date_arr[2] . '-' . $date_arr[1] . '-' . $date_arr[0]);
                        $interval = $now->diff($date);
                        $diffDays = intval($interval->format('%R%a'));

                        if ($diffDays <= $diffDaysMax && $diffDays >= 0) {
                            $volontaire1 = $row_csv[3];
                            $volontaire2 = $row_csv[4];
                            $motifAnnulation = $row_csv[5];
                            $annulation = $diffDays === 0 && $motifAnnulation;

                            if (
                                (!$volontaire1 || !$volontaire2) && !$motifAnnulation
                                || $annulation
                            ) {
                                $permanence = [
                                    'annulation' => $annulation,
                                    'date' => $date_str,
                                    'date_diff' => $diffDays + 1,
                                    'volontaire_ok' => $volontaire1 ? $volontaire1 : $volontaire2,
                                    'need_count' => !$volontaire1 && !$volontaire2 ? 2 : 1,
                                    'motif_annulation' => $motifAnnulation,
                                    'notes' => $row_csv[6],
                                    'url' => $url_link,
                                ];
                            }
                        }
                    }
                }
            }

            if ($permanence !== null) {
                if (count($mails_address) > 0) {
                    $email = (new Email())
                        ->from($this->params->get('MAILER_FROM'))
                        ->to($mail_to)
                        ->bcc(...$mails_address)
                        ->subject(
                            '[Compost] ' .
                            ($annulation ? 'Permanence annulé' : 'Besoin pour la prochaine permanence')
                        )
                        ->text(
                            $this->templating->render(
                                'email/compost.txt.twig',
                                $permanence
                            )
                        );

                    $this->mailer->send($email);

                    $io->success(count($mails_address) . ' mails envoyé');
                } else {
                    $io->error('Aucun mail dans la liste');
                }
            } else {
                $io->success('Aucun mail envoyé');
            }

        } catch (Exception $e) {
            $email = (new Email())
                ->from($this->params->get('MAILER_FROM'))
                ->to($this->params->get('MAILER_CC'))
                ->subject(
                    '[Compost] Erreur dans la commande'
                )
                ->text(
                    $e
                );

            $this->mailer->send($email);
        }

        return 0;
    }
}
