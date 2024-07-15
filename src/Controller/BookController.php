<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Requests\BookCreateRequest;
use App\Requests\BookUpdateRequest;
use App\Dto\BookReturnDTO;
use App\Service\BookService;

#[Route('api/books')]
class BookController extends AbstractController
{
    #[Route('/', methods: ['GET', 'HEAD'])]
    public function list(BookService $bookService, Request $request): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $paginator = $bookService->paginateAll($pageSize, $page);

        $data = [];
        foreach ($paginator as $author) {
           $data[] = BookReturnDTO::transform($author);
        }
    
        return $this->json(
            [ 
                'data' => $data, 
                'count' => count($paginator), 
                'pages' => ceil(count($paginator) / $pageSize), 
                'page' => $page,
            ]
        );
    }

    #[Route('/{id}', methods: ['GET', 'HEAD'], requirements: ['id' => '\d+'])]
    public function show(BookService $bookService, int $id): JsonResponse
    {
        $book = $bookService->getOne($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
            
        return $this->json(BookReturnDTO::transform($book));
    }

    #[Route('/{author}', methods: ['GET', 'HEAD'])]
    public function search(
        BookService $bookService, 
        Request $request, 
        string $author
    ): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $searchResult = $bookService->searchByAuthor($author, $pageSize, $page);
        
        $data = [];
        foreach($searchResult['books'] as $book) {
            $data[] = BookReturnDTO::transform($book);
        }
        
        return $this->json(
            [ 
                'data' => $data, 
                'count' => $searchResult['count'], 
                'pages' => ceil($searchResult['count'] / $pageSize), 
                'page' => $page,
            ]
        );
    }

    #[Route('/', methods: ['POST'])]
    public function create(
        BookCreateRequest $request, 
        BookService $bookService,
    ): JsonResponse
    {
        $book = $bookService->create($request);
            
        return $this->json(BookReturnDTO::transform($book));
    }

    // It's wrong but I don't see other way to deal with it. PHP doesn't know what is PATCH and 
    // how to work with it. This is sad(((
    #[Route('/{id}', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id, 
        BookUpdateRequest $request, 
        BookService $bookService
    ): JsonResponse
    {
        $book = $bookService->update($id, $request);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
            
        return $this->json(BookReturnDTO::transform($book));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id, BookService $bookService): JsonResponse
    {
        $success = $bookService->delete($id);
    
        if (!$success) {
            return $this->json('No book found for id ' . $id, 404);
        }
    
        return $this->json('Successfully deleted a book with id ' . $id);
    }
}