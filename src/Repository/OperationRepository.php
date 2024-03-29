<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    public function listYears(): array
    {
        return $this->createQueryBuilder('o')
            ->select('DISTINCT ToChar(o.date, \'YYYY\') AS date')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findDateNameAmount($date, $name, $amount)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.date = :date')
            ->andWhere('o.name = :name')
            ->andWhere('o.amount = :amount')
            ->setParameter('date', $date)
            ->setParameter('name', $name)
            ->setParameter('amount', $amount)
            ->getQuery()
            ->getResult();
    }

    public function getTotalsByYearAndType($type = null)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.amount) total')
            ->addSelect('ToChar(o.date, \'YYYY\') AS year')
            ->where('o.type != :hidden')->setParameter('hidden', OperationTypeEnum::Hidden)
            ->orderBy('year', 'asc')
            ->addGroupBy('year');

        if ($type) {
            $query
                ->addSelect('o.label')
                ->addGroupBy('o.label')
                ->andWhere('o.type = :type')->setParameter('type', $type)
                ->orderBy('o.label', 'asc');
        } else {
            $query
                ->addSelect('o.type')
                ->addGroupBy('o.type')
                ->orderBy('o.type', 'asc')
                ->andWhere('o.type != :other')->setParameter('other', OperationTypeEnum::Other);
        }

        return $query->getQuery()->getResult();
    }

    public function getTotalsByMonthAndType($year = null, $type = null)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.amount) total')
            ->addSelect('o.type')
            ->addSelect('ToChar(o.date, \'YYYY-MM\') AS date')
            ->where('o.type != :hidden')->setParameter('hidden', OperationTypeEnum::Hidden)
            ->orderBy('date', 'asc')
            ->addGroupBy('date')
            ->addGroupBy('o.type');

        if (!$type) {
            $query->andWhere('o.type != :other')->setParameter('other', OperationTypeEnum::Other);
        }

        if ($year) {
            $query->andWhere('ToChar(o.date, \'YYYY\') = :year')->setParameter('year', $year);
        }

        if ($type) {
            $query->andWhere('o.type = :type')->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    public function getTotalsByMonthAndLabel($year = null, $type = null)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.amount) total')
            ->addSelect('o.label')
            ->addSelect('ToChar(o.date, \'YYYY-MM\') AS date')
            ->where('o.type != :hidden')->setParameter('hidden', OperationTypeEnum::Hidden)
            ->orderBy('date', 'asc')
            ->addGroupBy('date')
            ->addGroupBy('o.label');

        if ($year) {
            $query->andWhere('ToChar(o.date, \'YYYY\') = :year')->setParameter('year', $year);
        }

        if ($type) {
            $query->andWhere('o.type = :type')->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    public function getTotalsByLabel(int $year, int $month, string $type = null)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.amount) total')
            ->andWhere('ToChar(o.date, \'YYYY\') = :year')->setParameter('year', $year)
            ->andWhere('ToChar(o.date, \'MM\') = :month')->setParameter('month', $month < 10 ? '0' . $month : $month);
        $query->andWhere(
            $query->expr()->notIn('o.type', [
                OperationTypeEnum::Income->value, OperationTypeEnum::Refund->value, OperationTypeEnum::Hidden->value
            ])
        );

        if ($type) {
            $query
                ->addSelect('o.label')
                ->addGroupBy('o.label')
                ->andWhere('o.type = :type')->setParameter('type', $type)
                ->orderBy('o.label', 'asc');
        } else {
            $query
                ->addSelect('o.type')
                ->addGroupBy('o.type')
                ->orderBy('o.type', 'asc');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countNullTypes(): int
    {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) nullCount')
            ->where('o.type IS NULL');

        return $query->getQuery()->getSingleScalarResult();
    }
}
