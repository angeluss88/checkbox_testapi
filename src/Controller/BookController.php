<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Requests\BookCreateRequest;
use App\Requests\BookUpdateRequest;
use App\Dto\BookWithAuthorsReturnDTO;
use App\Service\BookService;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Response\ValidationErrorResponse;

#[Route('api/books')]
class BookController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the books',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: BookWithAuthorsReturnDTO::class))
        )
    )]
    #[OA\Parameter(
        name: 'pageSize',
        in: 'query',
        description: 'The amount of books per page',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'The page number',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Books')]
    public function list(BookService $bookService, Request $request): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $paginator = $bookService->paginateAll($pageSize, $page);

        $data = [];
        foreach ($paginator as $author) {
           $data[] = BookWithAuthorsReturnDTO::transform($author);
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

    #[Route('/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the books',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: BookWithAuthorsReturnDTO::class))
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
        content: new OA\JsonContent(
            type: 'string',
            example: 'No book found for id 1'
        )
    )]
    #[OA\Tag(name: 'Books')]
    public function show(BookService $bookService, int $id): JsonResponse
    {
        $book = $bookService->getOne($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
            
        return $this->json(BookWithAuthorsReturnDTO::transform($book));
    }

    #[Route('/{author}', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the books by author lastname',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: BookWithAuthorsReturnDTO::class))
        )
    )]
    #[OA\Tag(name: 'Books')]
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
            $data[] = BookWithAuthorsReturnDTO::transform($book);
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
    #[OA\Response(
        response: 201,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: BookWithAuthorsReturnDTO::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad Request',
        content: new OA\JsonContent(ref: new Model(type: ValidationErrorResponse::class))
    )]
    
    #[OA\RequestBody(
        content: [new OA\MediaType(mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Book"),
                    new OA\Property(property: "short_description", type: "string"),
                    new OA\Property(property: "image", type: "file", format: "binary"),
                    new OA\Property(property: "date_published", type: "string", example: "1994"),
                    new OA\Property(property: "authors", type: "string", example: "1,2")
                ],
                required: ["name", "image", "date_published"]
            )
        )],
    )]
    
    #[OA\Tag(name: 'Books')]
    public function create(
        BookCreateRequest $request, 
        BookService $bookService,
    ): JsonResponse
    {
        $book = $bookService->create($request);
            
        return $this->json(BookWithAuthorsReturnDTO::transform($book));
    }

    // It's wrong but I don't see other way to deal with it. PHP doesn't know what is PATCH and 
    // how to work with it. This is sad(((
    #[Route('/{id}', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: BookWithAuthorsReturnDTO::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad Request',
        content: new OA\JsonContent(ref: new Model(type: ValidationErrorResponse::class))
    )]
    
    #[OA\RequestBody(
        content: [new OA\MediaType(mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Book"),
                    new OA\Property(property: "short_description", type: "string"),
                    new OA\Property(property: "image", type: "file", format: "binary"),
                    new OA\Property(property: "date_published", type: "string", example: "1994"),
                    new OA\Property(property: "authors", type: "string", example: "1,2")
                ],
            )
        )],
    )]
    
    #[OA\Tag(name: 'Books')]
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
            
        return $this->json(BookWithAuthorsReturnDTO::transform($book));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'Deletes the books',
        content: new OA\JsonContent(
            type: 'string',
            example: 'Successfully deleted a book with id 1'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Not Found',
        content: new OA\JsonContent(
            type: 'string',
            example: 'No book found for id 1'
        )
    )]
    #[OA\Tag(name: 'Books')]
    public function delete(int $id, BookService $bookService): JsonResponse
    {
        $success = $bookService->delete($id);
    
        if (!$success) {
            return $this->json('No book found for id ' . $id, 404);
        }
    
        return $this->json('Successfully deleted a book with id ' . $id);
    }
}