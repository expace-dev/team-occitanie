<?php

namespace App\Repository;

use App\Entity\Taches;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Taches>
 *
 * @method Taches|null find($id, $lockMode = null, $lockVersion = null)
 * @method Taches|null findOneBy(array $criteria, array $orderBy = null)
 * @method Taches[]    findAll()
 * @method Taches[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TachesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Taches::class);
    }

    public function findTaches($page, $limit = 15) {
        $limit = abs($limit);

        $result = [];

        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('a')
            ->from('App\Entity\Taches', 'a')
            ->andWhere('a.statut = true')
            ->setMaxResults($limit)
            ->setFirstResult(($page * $limit) - $limit)
            ->orderBy('a.createdAt', 'DESC');

        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        
        
        if (empty($data)) {
            return $result;
        }

        $pages = ceil($paginator->count() / $limit);

        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page'] = $page;
        $result['limit'] = $limit;
        //dd($data);

        return $result;

    }

    //    /**
    //     * @return Taches[] Returns an array of Taches objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Taches
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
