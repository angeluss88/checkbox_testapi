<?php
namespace App\Service;

use App\Entity\Author;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use App\Requests\AuthorCreateRequest;

class AuthorService extends Service
{
    public function paginateAll(int $pageSize, int $page): Paginator
    {
        return $this->paginateEntities(Author::class, $pageSize, $page);
    }

    public function create(AuthorCreateRequest $request): Author
    {
        $author = new Author();
        $author->setFirstname($request->request->get('firstname'));
        $author->setLastname($request->request->get('lastname'));
        $author->setSecondaryname($request->request->get('secondaryname', ''));
    
        $this->entityManager->persist($author);
        $this->entityManager->flush();
            
        return $author;
    }
}