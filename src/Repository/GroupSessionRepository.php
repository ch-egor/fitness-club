<?php

namespace App\Repository;

use App\Entity\GroupSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GroupSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupSession[]    findAll()
 * @method GroupSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupSessionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GroupSession::class);
    }

//    /**
//     * @return GroupSession[] Returns an array of GroupSession objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GroupSession
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
