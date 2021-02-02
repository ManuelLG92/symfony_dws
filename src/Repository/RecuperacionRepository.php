<?php

namespace App\Repository;

use App\Entity\Recuperacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recuperacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recuperacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recuperacion[]    findAll()
 * @method Recuperacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecuperacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recuperacion::class);
    }

    public function findByIdUsuario($value) :? Recuperacion
    {
        return $this->createQueryBuilder('r')
            ->Where('r.id_usuario = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByToken($value) :? Recuperacion
    {
        return $this->createQueryBuilder('r')
            ->Where('r.token = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Recuperacion[] Returns an array of Recuperacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Recuperacion
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
