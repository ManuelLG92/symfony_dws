<?php

namespace App\Repository;

use App\Entity\Factura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Factura|null find($id, $lockMode = null, $lockVersion = null)
 * @method Factura|null findOneBy(array $criteria, array $orderBy = null)
 * @method Factura[]    findAll()
 * @method Factura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Factura::class);
    }

     /**
      * @return Factura[] Returns an array of Factura objects
     */
    public function findByUsuarioId($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.id_cliente = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    public function buscarFacturasPorUsuario($usuario, $pagina,$elementosPagina)
    {
        $queryDql = $this->createQueryBuilder('f')
            ->andWhere('f.id_cliente = :val')
            ->setParameter('val', $usuario)
            ->orderBy('f.id', 'DESC')
            ->getQuery();
        return $this->paginacionFacturas($queryDql,$pagina,$elementosPagina);
    }

    public function numeroFacturasPorCliente($id)
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->andWhere('f.id_cliente = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginacionFacturas ($dql, $pagina, $numeroElementos)
    {
        $paginador = new Paginator($dql);
        $paginador->getQuery()
            ->setFirstResult($numeroElementos * ($pagina-1))
            ->setMaxResults($numeroElementos);
        return $paginador;

    }

    public function importeGastoPorUsuario($idUsuario)
    {
        return $this->createQueryBuilder('f')
            ->select('sum(f.importe)')
            ->andWhere('f.id_cliente = :val')
            ->setParameter('val', $idUsuario)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?Factura
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
