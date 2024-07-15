<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Requests\AuthorCreateRequest;

#[Route('api/authors')]
class AuthorController extends AbstractController
{
    #[Route('/', methods: ['GET', 'HEAD'])]
    public function list(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $query = $entityManager->getRepository(Author::class)
            ->createQueryBuilder('u')
            ->getQuery();
    
        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $pageSize);

        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page-1)) 
            ->setMaxResults($pageSize); 
        
        $data = [];
        foreach ($paginator as $author) {
           $books = [];
           foreach($author->getBooks() as $book) {
                $books[] = [
                    'id' => $book->getId(),
                    'name' => $book->getName(),
                    'short_description' => $book->getShortDescription(),
                    'image' => $book->getImage(),
                    'date_published' => $book->getDatePublished(),
                ];
           } 
           $data[] = [
               'id' => $author->getId(),
               'lastname' => $author->getFirstname(),
               'firstname' => $author->getLastname(),
               'secondaryname' => $author->getSecondaryname(),
               'books' => $books,
           ];
        }
    
        return $this->json(
            [ 
                'data' => $data, 
                'count' => $totalItems, 
                'pages' => $pagesCount, 
                'page' => $page,
            ]
        );

    }

    #[Route('/', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, AuthorCreateRequest $request): JsonResponse
    {
        $author = new Author();
        $author->setFirstname($request->request->get('firstname'));
        $author->setLastname($request->request->get('lastname'));
        $author->setSecondaryname($request->request->get('secondaryname', ''));
    
        $entityManager->persist($author);
        $entityManager->flush();
    
        $data =  [
            'id' => $author->getId(),
            'lastname' => $author->getFirstname(),
            'firstname' => $author->getLastname(),
            'secondaryname' => $author->getSecondaryname(),
        ];
            
        return $this->json($data);
    }
}