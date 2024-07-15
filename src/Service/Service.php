<?php
namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use App\Requests\AuthorCreateRequest;
use Symfony\Component\Filesystem\Filesystem;

abstract class Service
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected FileUploader $fileUploader,
        protected Filesystem $filesystem
    ) {
    }

    public function paginateEntities($class, int $pageSize, int $page): Paginator
    {
        $query = $this->entityManager->getRepository($class)
            ->createQueryBuilder('u')
            ->getQuery();
    
        $paginator = new Paginator($query);

        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page-1)) 
            ->setMaxResults($pageSize); 
    
        return $paginator;
    }
}