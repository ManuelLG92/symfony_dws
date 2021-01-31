<?php

namespace App\Repository;

use App\Entity\Articulo;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Articulo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Articulo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Articulo[]    findAll()
 * @method Articulo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticuloRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articulo::class);
    }

    public function findBySeccionId($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id_seccion = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }



    public function findByIdVendedor($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id_vendedor = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function NumeroArticulosPorSeccion($value): ?int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->andWhere('a.id_seccion = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function buscarArticulosPorSeccion($seccion, $pagina,$elementosPagina)
    {
        $queryDql = $this->createQueryBuilder('a')
            ->andWhere('a.id_seccion = :val')
            ->setParameter('val', $seccion)
            ->orderBy('a.id', 'ASC')
            ->getQuery();
        return $this->paginacion($queryDql,$pagina,$elementosPagina);
    }

    public function paginacion ($dql, $pagina, $numeroElementos)
    {
        $paginador = new Paginator($dql);
        $paginador->getQuery()
            ->setFirstResult($numeroElementos * ($pagina-1))
            ->setMaxResults($numeroElementos);
        return $paginador;

    }




    // /**
    //  * @return Articulo[] Returns an array of Articulo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Articulo
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
