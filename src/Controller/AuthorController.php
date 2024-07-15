<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Requests\AuthorCreateRequest;
use App\Dto\AuthorReturnDTO;
use App\Service\AuthorService;

#[Route('api/authors')]
class AuthorController extends AbstractController
{
    #[Route('/', methods: ['GET', 'HEAD'])]
    public function list(AuthorService $authorService, Request $request): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $paginator = $authorService->paginateAll($pageSize, $page);

        $data = [];
        foreach ($paginator as $author) {
           $data[] = AuthorReturnDTO::transform($author);
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
    public function create(AuthorService $authorService, AuthorCreateRequest $request): JsonResponse
    {
        $author = $authorService->create($request);
            
        return $this->json(AuthorReturnDTO::transform($author, false));
    }
}