<?php
namespace ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function findByFilters(array $filters)
    {
        $qb = $this->createQueryBuilder('p');

        if (!empty($filters['name'])) {
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $filters['category']);
        }

        return $qb->getQuery()->getResult();
    }
}
