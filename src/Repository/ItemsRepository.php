<?php

namespace App\Repository;

use App\Entity\Items;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Items|null find($id, $lockMode = null, $lockVersion = null)
 * @method Items|null findOneBy(array $criteria, array $orderBy = null)
 * @method Items[]    findAll()
 * @method Items[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Items::class);
    }

    public function findItemsByCarroId($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id_carro = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findItemsByCarroIdAndArticuloId($value,$articulo)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id_carro = :val')
            ->setParameter('val', $value)
            ->andWhere('a.id_articulo = :val2')
            ->setParameter('val2', $articulo)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function ItemsPorUsuario($value): ?int
    {
        return $this->createQueryBuilder('i')
            ->select('count(i.id)')
            ->andWhere('i.id_carro = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult();

    }


    // /**
    //  * @return Items[] Returns an array of Items objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Items
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
