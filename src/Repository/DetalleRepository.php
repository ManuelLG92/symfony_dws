<?php

namespace App\Repository;

use App\Entity\Detalle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Detalle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Detalle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Detalle[]    findAll()
 * @method Detalle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Detalle::class);
    }
/**/
    /**
     * @return Detalle[] Returns an array of Detalle objects
     */

    public function findByFacturaId($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.numero_factura = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }

    public function ArticulosPorVendedor($value)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id_articulo, sum(d.total),sum(d.cantidad)')
            ->andWhere('d.id_vendedor = :val')
            ->setParameter('val', $value)
            ->groupBy('d.id_articulo')
            ->getQuery()
            ->getResult();

    }

    public function findVentasByArticuloId($value)
    {
        return $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->andWhere('d.id_articulo = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
/*
    // /**
    //  * @return Detalle[] Returns an array of Detalle objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Detalle
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
