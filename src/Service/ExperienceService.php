<?php

namespace App\Service;

use App\Entity\Experience;
use Doctrine\ORM\EntityManagerInterface;

class ExperienceService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function update(array $ids): void
    {
        $repository = $this->entityManager->getRepository(Experience::class);

        foreach ($ids as $id) {
            /** @var Experience $experience */
            $experience = $repository->find($id);

            $search = [];
            if ($experience->getCompany()) {
                $search[] = $experience->getCompany()->getDisplayName();
            }
            if ($experience->getClient()) {
                $search[] = $experience->getClient()->getDisplayName();
            }
            if ($experience->getDateBegin()) {
                $search[] = $experience->getDateBegin()->format('dd/MM/yyyy');
            }
            if ($experience->getDateEnd()) {
                $search[] = $experience->getDateEnd()->format('dd/MM/yyyy');
            }

            $experience->setSearch(implode(' - ', $search));
        }
        $this->entityManager->flush();
    }
}
