<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

       /**
        * @return Book[] Returns an array of Book objects
        */
       public function findByAuthorAndPaginate($author, $page = 1, $pageSize = 5): array
       {
           return $this->createQueryBuilder('b')
           ->innerJoin('b.authors', 'a', 'WITH', 'a.lastname = :author')
           ->setParameter('author', $author)
           ->orderBy('b.id', 'ASC')
           ->setFirstResult($pageSize * ($page-1)) 
           ->setMaxResults($pageSize)
           ->getQuery()
           ->getResult();
       }

       /**
        * @return Book[] Returns an array of Book objects
        */
        public function countdByAuthor($author): int
        {
            return count($this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->innerJoin('b.authors', 'a', 'WITH', 'a.lastname = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult());
        }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}