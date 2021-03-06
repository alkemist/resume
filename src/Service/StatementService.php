<?php

namespace App\Service;


use App\Entity\Operation;
use App\Entity\Statement;
use App\Helper\StringHelper;
use App\Repository\OperationFilterRepository;
use App\Repository\OperationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Smalot\PdfParser\Parser;

class StatementService
{
    public function __construct(
        private readonly string                    $statementDirectory,
        private readonly EntityManagerInterface    $entityManager,
        private readonly OperationRepository       $operationRepository,
        private readonly OperationFilterRepository $operationFilterRepository
    ) {
    }

    public function get(Statement $statement): bool|string
    {
        return $this->statementDirectory . $statement->getFilename();
    }

    /**
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function extractOperations(Statement $statement, bool $throwError): void
    {
        $filePath = $this->statementDirectory . $statement->getFilename();
        $operations = [];
        $logPositives = $logNegatives = [];

        // Note : Impossible de diférencier les débits et crédits
        $PDFParser = new Parser();
        $pdf = $PDFParser->parseFile($filePath);
        $text = $pdf->getText();

        $lines = explode("\n", $text);
        $startAmount = $endAmount = $totalAmount = $nbOperations = 0;

        $extractOperations = false;

        foreach ($lines as $index => $line) {
            if ($extractOperations && $index > $extractOperations) {
                if (str_starts_with($line, "Date\tDate valeur\tOpération\tDébit EUROS\tCrédit EUROS\t")) {
                    $extractOperations = false;
                } else {
                    $operation = explode("\t", $line);
                    if (preg_match('#\d{2}/\d{2}/\d{4}#', $operation[0])) {
                        $date = DateTime::createFromFormat('d/m/Y', $operation[0]);

                        $operations[] = [
                            $date,
                            $operation[2],
                            StringHelper::extractAmount($operation[3])
                        ];
                    } else {
                        $operations[count($operations) - 1][1] .= ' - ' . $operation[0];
                    }
                }
            } elseif (str_starts_with($line, 'Compte Courant JEUNE ACTIF N°')
                || str_starts_with($line, 'C/C EUROCOMPTE DUO CONFORT N°')) {
                $extractOperations = $index + 1;
            } elseif (strpos($line, 'SOLDE CREDITEUR ') > -1) {
                $lineArray = explode("\t", $line);
                $amount = StringHelper::extractAmount(
                    str_starts_with($line, 'SOLDE CREDITEUR ') ? $lineArray[1] : $lineArray[2]
                );

                if ($startAmount && !$endAmount) {
                    $endAmount = $amount;
                } elseif (!$startAmount) {
                    $startAmount = $amount;
                }
            }
        }

        $positiveFilters = array_column($this->operationFilterRepository->getPositiveFilters(), 'name');
        $positiveExceptionFilters = $this->operationFilterRepository->getPositiveExceptionFilters();
        $filters = $this->operationFilterRepository->getFilters();
        $history = [];

        foreach ($operations as $operationLine) {
            /** @var DateTime $date */
            $date = $operationLine[0];
            $name = $operationLine[1];
            $amount = $operationLine[2];
            $isPositiv = false;
            $log = $date->format('Ymd') . ' ' . $name . ' ' . $amount;

            if (StringHelper::contains($name, $positiveFilters) === true) {
                $isPositiv = true;
            } else {
                foreach ($positiveExceptionFilters as $exception) {
                    if (strpos($name, (string)$exception['name']) > -1
                        && $date->format('d/m/Y') === $exception['date']->format('d/m/Y')
                        && $amount == floatval($exception['amount'])
                        && !in_array($log, $history)) {
                        $isPositiv = true;
                        break;
                    }
                }
            }

            $amount = $isPositiv ? $amount : -$amount;
            $totalAmount += $amount;
            $history[] = $log;

            if ($amount > 0) {
                $logPositives[] = $name . ' : ' . $amount;
            } else {
                $logNegatives[] = $name . ' : ' . $amount . ' : ' . $date->format('d/m/Y');
            }

            if (!$this->operationRepository->findDateNameAmount($date, $name, $amount)) {
                $operation = new Operation();
                $operation->setDate($date);
                $operation->setName($name);
                $operation->setAmount($amount);

                $this->analyseOperation($operation, $filters);

                $this->entityManager->persist($operation);
                $nbOperations++;
            }
        }

        if (round($startAmount + $totalAmount, 2) != round($endAmount, 2)) {
            if ($throwError) {
                if (isset($date)) {
                    dump('Date : ' . $date->format('d/m/Y'));
                }
                dump("Montant de départ : " . $startAmount);
                dump("Montant de fin : " . $endAmount);
                dump("Total calculé : " . ($startAmount + $totalAmount));
                dump("Différence : " . round($endAmount - ($startAmount + $totalAmount), 2));
                dump("Valeurs positives");
                dump($logPositives);
                dump("Valeurs negatives");
                dump($logNegatives);

                exit;
            }
        } else {
            $statement->setOperationsCount(count($operations));
            $this->entityManager->flush();
        }

    }

    public function analyseOperation(Operation $operation, array $filters): void
    {
        if (
            preg_match('#PAIEMENT\s+(PSC|CB)\s+[\d\s]+\s+([A-Za-z\s]*)\s+-#', $operation->getName(), $matches)
            && count($matches) == 3) {
            $operation->setLocation(trim(str_replace('FR ', '', (string)$matches[2])));
        }
        if (!$operation->getLabel()) {
            $operation->setLabel(trim(str_replace('CARTE 12946058', '', $operation->getName())));
        }

        foreach ($filters as $filter) {
            if (preg_match('#' . $filter['name'] . '#i', $operation->getName(), $matches)) {
                $operation->setType($filter['type']);
                $operation->setTarget($filter['target']);
                $operation->setLabel($filter['label']);
                break;
            }
        }
    }
}
