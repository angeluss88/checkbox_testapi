<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Requests\AuthorCreateRequest;
use App\Service\AuthorService;
use App\Dto\AuthorWithBooksReturnDTO;
use App\Dto\AuthorWithoutBooksReturnDTO;
use App\Dto\AuthorCreateDto;
use OpenApi\Attributes as OA;
use App\Entity\Author;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Response\ValidationErrorResponse;

#[Route('api/authors')]
class AuthorController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the authors',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: AuthorWithBooksReturnDTO::class))
        )
    )]
    #[OA\Parameter(
        name: 'pageSize',
        in: 'query',
        description: 'The amount of authors per page (default 5)',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'The page number (default 1)',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Authors')]
    public function list(AuthorService $authorService, Request $request): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $paginator = $authorService->paginateAll($pageSize, $page);

        $data = [];
        foreach ($paginator as $author) {
           $data[] = AuthorWithBooksReturnDTO::transform($author);
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

    #[Route('/', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: AuthorWithoutBooksReturnDTO::class))
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
                    // new OA\Property(property: "upload", type: "file", format: "binary"),
                    new OA\Property(property: "firstname", type: "string", example: "John"),
                    new OA\Property(property: "lastname", type: "string", example: "Doe"),
                    new OA\Property(property: "secondaryname", type: "string", example: "Petrovich")
                ],
                required: ["firstname", "lastname"]
            )
        )],
    )]
    
    #[OA\Tag(name: 'Authors')]
    public function create(AuthorService $authorService, AuthorCreateRequest $request): JsonResponse
    {
        $author = $authorService->create($request);
            
        return $this->json(AuthorWithoutBooksReturnDTO::transform($author), 201);
    }
}