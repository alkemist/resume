<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Enum\InvoiceStatusEnum;
use App\Helper\StringHelper;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Konekt\PdfInvoice\InvoicePrinter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceService
{
    public function __construct(
        public string                           $pdfFileDirectory, public string $companyName,
        public string                           $companyStreet, public string $companyCity,
        public string                           $companySiret, public string $companyApe,
        public string                           $companyStatut, public string $companyTva,
        private readonly EntityManagerInterface $entityManager,
        private readonly InvoiceRepository      $invoiceRepository,
        private readonly ExperienceRepository   $experienceRepository,
        private readonly PeriodService          $periodService,
        private readonly SerializerInterface    $serializer,
        private readonly TranslatorInterface    $translator
    )
    {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countWaitingInvoices(): int
    {
        return $this->invoiceRepository->countWaiting();
    }

    /**
     * Crée ou récupère le fichier pdf actuellement stocké
     * @throws Exception
     */
    public function getOrCreatePdf(Invoice $invoice, bool $force = false): string
    {
        if (!$force && file_exists($this->pdfFileDirectory . $invoice->getFilename())) {
            return file_get_contents($this->pdfFileDirectory . $invoice->getFilename());
        }

        $pdf = $this->createPdf($invoice);
        $pdf->render($this->pdfFileDirectory . $invoice->getFilename(), 'F');

        return $this->pdfFileDirectory . $invoice->getFilename();
    }

    /**
     * Crée un facture pdf à partir d'un objet facture
     * @throws Exception
     */
    public function createPdf(Invoice $invoice): InvoicePrinter
    {
        $pdfInvoice = new InvoicePrinter('A4', '€', 'fr');

        $pdfInvoice->lang['date'] = 'Facturation';
        $pdfInvoice->lang['due'] = 'Echéance';

        /* Header settings */
        $pdfInvoice->setColor("#5cb85c");      // pdf color scheme
        $pdfInvoice->setType(
            StringHelper::encode("Facture n° " . $invoice->getNumber() . ($invoice->isCredit() ? ' (Avoir)' : ''))
        );                                     // Invoice Type
        $pdfInvoice->setNumberFormat(',', ' ');
        $pdfInvoice->setReference($invoice->getNumber());                  // Reference
        $pdfInvoice->setDate($invoice->getCreatedAt()->format('d/m/Y'));   //Billing Date
        $pdfInvoice->setDue($invoice->getDueAt()->format('d/m/Y'));        //Billing Date

        $pdfInvoice->setFrom([
            StringHelper::encode($this->companyName),
            '',
            StringHelper::encode($this->companyStreet),
            StringHelper::encode($this->companyCity)
        ]);
        $pdfInvoice->setTo([
            StringHelper::encode($invoice->getCompany()->getName()),
            StringHelper::encode($invoice->getCompany()->getService()),
            StringHelper::encode($invoice->getCompany()->getStreet()),
            StringHelper::encode(
                $invoice->getCompany()->getPostalCode() . ' ' . $invoice->getCompany()->getCity()
            )
        ]);

        if ($invoice->getDaysCount()) {
            $pdfInvoice->addItem(
                StringHelper::encode("Journée de prestation"),
                "",
                $invoice->getDaysCount(),
                $invoice->getTotalTax() ? (Invoice::TAX_MULTIPLIER * 100) . "%" : '',
                $invoice->getTjm(),
                '',
                $invoice->getTotalHt()
            );
        }

        if ($invoice->getExtraLibelle() && $invoice->getExtraHt()) {
            $pdfInvoice->addItem(
                StringHelper::encode($invoice->getExtraLibelle()),
                "",
                "",
                $invoice->getTotalTax() ? (Invoice::TAX_MULTIPLIER * 100) . "%" : '',
                $invoice->getExtraHt(),
                '',
                $invoice->getExtraHt()
            );
        }

        $pdfInvoice->AliasNbPages('');
        $pdfInvoice->addTotal("Total HT", $invoice->getTotalHt());
        $pdfInvoice->addTotal("TVA " . (Invoice::TAX_MULTIPLIER * 100) . "%", $invoice->getTotalTax());
        $pdfInvoice->addTotal("Total TTC", $invoice->getTotalTtc(), true);

        if ($invoice->getReference()) {
            $pdfInvoice->addTitle(StringHelper::encode('Référence externe : ' . $invoice->getReference()));
        }

        $pdfInvoice->addTitle("Règlement");
        $pdfInvoice->addParagraph(
            StringHelper::encode(
                "
            RIB : 10278 07374 00020438301 93
            IBAN : FR76 1027 8073 7400 0204 3830 193
            BIC : CMCIFR2A
        "
            )
        );

        $pdfInvoice->addTitle(StringHelper::encode("Informations légales"));
        $legalInformations = "
            Taux des pénalités en cas de retard de paiement : taux directeur de refinancement de la BCE, majoré de 10 points
            En cas de retard de paiement, indemnité forfaitaire légale pour frais de recouvrement : 40,00 EUR
            Escompte en cas de paiement anticipé : aucun
          
            Dispensé d'immatriculation au Registre du Commerce et des Sociétés et au Répertoire des Métiers";

        if (!$invoice->getTotalTax()) {
            $legalInformations = "
            TVA non applicable, art. 293B du CGI
            " . $legalInformations;
        }

        $pdfInvoice->addParagraph(StringHelper::encode($legalInformations));

        $footerInformations = [
            $this->companyName,
            'Siret : ' . $this->companySiret,
            'APE : ' . $this->companyApe,
            $this->companyStatut,
            'Numero TVA : ' . $this->companyTva
        ];

        $pdfInvoice->setFooternote(StringHelper::encode(implode(' - ', $footerInformations)));

        return $pdfInvoice;
    }

    /**
     * Génère le livre de recette en CSV
     */
    public function generateInvoicesBook(): string
    {
        $invoices = $this->invoiceRepository->findInvoicesBy(null, null, true);

        $columns = ["Date d'encaissement",
            "Référence de la facture",
            "Nom du client",
            "Nature de la vente",
            "Montant de la vente",
            "Mode d'encaissement"];
        $dataCsv = [];

        foreach ($invoices as $invoice) {
            $dataCsv[] = [
                $columns[0] => $invoice->getPayedAt() ? $invoice->getPayedAt()->format('d/m/Y') : '',
                $columns[1] => $invoice->getNumber(),
                $columns[2] => $invoice->getCompany()->getName(),
                $columns[3] => $invoice->getObject(),
                $columns[4] => $invoice->getTotalHt(),
                $columns[5] => $this->translator->trans($invoice->getPayedByName())
            ];
        }

        $filename = $this->pdfFileDirectory . 'livre-recettes.csv';

        file_put_contents(
            $filename,
            $this->serializer->encode($dataCsv, 'csv', [
                'csv_delimiter' => ';'
            ])
        );

        return $filename;
    }

    /**
     * Met à jour la période d'une facture
     * @throws Exception
     */
    public function updatePeriod(Invoice $invoice): void
    {
        $date = $invoice->getPayedAt();
        if ($date) {
            [, $quarterlyPeriod] = $this->periodService->getCurrentPeriod($date);
            $invoice->setPeriod($quarterlyPeriod);
        }
    }

    /**
     * Crée une factures à partir d'un rapport d'activité
     * @throws NonUniqueResultException|NoResultException
     */
    public function createByActivities(DateTime $currentDate, Company $company, array $activities): Invoice
    {
        $currentDate->modify('last day of this month');

        $invoice = new Invoice($currentDate);
        $invoice->setCompany($company);
        $invoice->importActivities($activities);

        $experiences = $this->experienceRepository->findByDate($currentDate);
        if ($experiences && count($experiences) == 1) {
            $invoice->setExperience($experiences[0]);
        }

        if ($company->getTjm()) {
            $invoice->setTjm($company->getTjm());
            $invoice->updateHt();
        }

        if ($company->getReference()) {
            $invoice->setReference($company->getReference());
        }

        $this->calculTva($invoice);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    /**
     * Calcul la TVA d'une facture
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function calculTva(Invoice $invoice): void
    {
        $isOutOfTaxLimit = $this->invoiceRepository->isOutOfTaxLimit($invoice->getTotalHt());

        if ($isOutOfTaxLimit) {
            $invoice->setTotalTax($invoice->getTotalHt() * Invoice::TAX_MULTIPLIER);
        }
    }

    /**
     * Envoi d'un mail si la date d'échéance d'une facture approche et qu'elle n'est toujours pas indiqué comme payé
     * @throws Exception
     */
    public function getNotifications(): array
    {
        $date = new DateTime();
        $year = intval($date->format('Y'));
        $month = intval($date->format('m'));
        $day = intval($date->format('d'));
        $notifications = [];

        $currentExperiences = $this->experienceRepository->getCurrents();
        if ($currentExperiences > 0) {
            foreach ($currentExperiences as $experience) {
                $lastInvoice = $this->invoiceRepository->getByCompanyLast($experience->getCompany(), $date);

                // Première facture
                $sendNotification = $lastInvoice === null && $day > 20;

                if ($lastInvoice) {
                    [$invoiceYear, $invoiceMonth] = $lastInvoice->getYearMonth();
                    $sendNotification = $invoiceYear < $year
                        || $invoiceMonth < $month && (($month > $invoiceMonth + 1) || $day > 20);
                }

                if ($sendNotification) {
                    $notifications[] = 'Facture à créer pour ' . $experience->getCompany()->getName() .
                        ' pour le mois de ' . $this->translator->trans($date->format('F'));
                }

                $notSentInvoices = $this->invoiceRepository
                    ->getByCompanyAndStatus($experience->getCompany(), [
                        InvoiceStatusEnum::Draft->value, InvoiceStatusEnum::Waiting->value
                    ]);

                foreach ($notSentInvoices as $invoice) {
                    [$invoiceYear, $invoiceMonth] = $lastInvoice->getYearMonth();
                    $sendNotification = $invoiceYear < $year
                        || $invoiceMonth <= $month && (($month > $invoiceMonth) || $day > 20);

                    if ($sendNotification) {
                        $notifications[] = 'Facture à envoyer à ' . $experience->getCompany()->getName() .
                            ' pour le mois de ' . $this->translator->trans($invoice->getDateMonth()->format('F'));
                    }
                }
            }
        }

        $unpayedInvoices = $this->invoiceRepository->findInvoicesBy(null, null, false);
        if (count($unpayedInvoices) > 0) {
            foreach ($unpayedInvoices as $invoice) {
                if ($invoice->getDueAt() && $invoice->getDueAt() < $date) {
                    $notifications[] = 'Facture ' . $invoice->getNumber() .
                        ' de ' . $invoice->getTotalTtc() . '€ TTC à encaisser depuis le ' . $invoice->getDueAt()->format('d/m/Y');
                }
            }
        }

        return $notifications;
    }
}
