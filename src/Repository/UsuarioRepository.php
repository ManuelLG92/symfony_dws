<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository
    //implements UserInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    public function paginacion ($dql, $pagina, $numeroElementos)
    {
     $paginador = new Paginator($dql);
     $paginador->getQuery()
         ->setFirstResult($numeroElementos * ($pagina-1))
         ->setMaxResults($numeroElementos);
     return $paginador;

    }

    public function buscarUsuarios($pagina = 1,$elementosPagina = 5)
    {
        $queryDql = $this->createQueryBuilder('u')
            ->getQuery();
        return $this->paginacion($queryDql,$pagina,$elementosPagina);
    }

    public function findByEmail($value)
    {
        return $this->createQueryBuilder('u')
                ->Where('u.email = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getResult();
  /*          $qb = $em->createQueryBuilder()
                ->select('u')
                ->from('Userario', 'u')
                ->where('u.email LIKE ?')
                ->andWhere('u.is_active = 1');
            ->andWhere('u.email = :val')*/


    }
    public function findById($value)
    {
        return $this->createQueryBuilder('u')
            ->Where('u.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ;
    }

    public function findOneByIdVendedor($value): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.vendedor_id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /*          $qb = $em->createQueryBuilder()
                  ->select('u')
                  ->from('Userario', 'u')
                  ->where('u.email LIKE ?')
                  ->andWhere('u.is_active = 1');
              ->andWhere('u.email = :val')*/




    // /**
    //  * @return Usuario[] Returns an array of Usuario objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
/*    public function getRoles()
    {
        // TODO: Implement getRoles() method.
    }

    public function getPassword($clave)
    {
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getUsername()
    {
        // TODO: Implement getUsername() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }*/
}
