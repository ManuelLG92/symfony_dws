<?php

namespace App\Repository;

use App\Entity\Valoracion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Valoracion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Valoracion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Valoracion[]    findAll()
 * @method Valoracion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValoracionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Valoracion::class);
    }

    /**
     * @return Valoracion[] Returns an array of Valoracion objects
     */

    public function findByIdComprador($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.id_cliente = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findValoracionItemsById($value)
    {
        return $this->createQueryBuilder('v')
            ->select('sum(v.valor)/count(v.id_articulo)')
            ->andWhere('v.id_articulo = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findValoracionVendedorById($value)
    {
        return $this->createQueryBuilder('v')
            ->select('sum(v.valor)/count(v.id_vendedor)')
            ->andWhere('v.id_vendedor = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function findValoracionByClientArticuloAndFacturaId($clienteId,$articuloId,$facturaId)
    {
        return $this->createQueryBuilder('v')

            ->andWhere('v.id_articulo = :val')
            ->setParameter('val', $articuloId)
            ->andWhere('v.id_cliente = :val2')
            ->setParameter('val2', $clienteId)
            ->andWhere('v.numero_factura = :val3')
            ->setParameter('val3', $facturaId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }



   /* public function findValoracionByIdAndArticuloId($id,$articuloId)
    {
        return $this->createQueryBuilder('v')

            ->andWhere('v.id = :val')
            ->setParameter('val', $id)
            ->andWhere('v.id_articulo = :val2')
            ->setParameter('val2', $articuloId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }*/





    /*
    public function findOneBySomeField($value): ?Valoracion
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
