<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Book;
use App\Entity\Author;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Filesystem;
use App\Requests\BookCreateRequest;
use App\Requests\BookUpdateRequest;

#[Route('api/books')]
class BookController extends AbstractController
{
    #[Route('/', methods: ['GET', 'HEAD'])]
    public function list(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);

        $query = $entityManager->getRepository(Book::class)
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
        foreach ($paginator as $book) {
            $authors = [];
            foreach($book->getAuthors() as $author) {
                $authors[] = [
                    'id' => $author->getId(),
                    'lastname' => $author->getFirstname(),
                    'firstname' => $author->getLastname(),
                    'secondaryname' => $author->getSecondaryname(),
                ];
            }
            $data[] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'short_description' => $book->getShortDescription(),
                'image' => $book->getImage(),
                'date_published' => $book->getDatePublished(),
                'authors' => $authors,
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

    
        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET', 'HEAD'], requirements: ['id' => '\d+'])]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }

        $authors = [];
        foreach($book->getAuthors() as $author) {
            $authors[] = [
                'id' => $author->getId(),
                'lastname' => $author->getFirstname(),
                'firstname' => $author->getLastname(),
                'secondaryname' => $author->getSecondaryname(),
            ];
        }
    
        $data =  [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'short_description' => $book->getShortDescription(),
            'image' => $book->getImage(),
            'date_published' => $book->getDatePublished(),
            'authors' => $authors,
        ];
            
        return $this->json($data);
    }

    #[Route('/{author}', methods: ['GET', 'HEAD'])]
    public function search(Request $request, EntityManagerInterface $entityManager, string $author): JsonResponse
    {
        $pageSize = (int) $request->query->get('pageSize', 5);
        $page = (int) $request->query->get('page', 1);
        
        $books = $entityManager->getRepository(Book::class)->findByAuthorAndPaginate($author, $page, $pageSize);

        $data = [];
        foreach($books as $book) {
            $authors = [];
            foreach($book->getAuthors() as $author) {
                $authors[] = [
                    'id' => $author->getId(),
                    'lastname' => $author->getFirstname(),
                    'firstname' => $author->getLastname(),
                    'secondaryname' => $author->getSecondaryname(),
                ];
            }
            $data[] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'short_description' => $book->getShortDescription(),
                'image' => $book->getImage(),
                'date_published' => $book->getDatePublished(),
                'authors' => $authors,
            ];
        }
        
        $totalItems = $entityManager->getRepository(Book::class)->countdByAuthor($author);
        $pagesCount = ceil($totalItems / $pageSize);
    
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
    public function create(
        EntityManagerInterface $entityManager, 
        BookCreateRequest $request, 
        FileUploader $fileUploader
    ): JsonResponse
    {
        $book = new Book();
        $book->setName($request->request->get('name'));
        $book->setShortDescription($request->request->get('short_description', ''));

        $image = $request->files->get('image');
        if($image) {
            $fileName = $fileUploader->upload($image);
            $book->setImage($fileName);
        }

        $book->setDatePublished($request->request->get('date_published'));

        $authorIds = array_map('intval', explode(',', $request->request->get('authors')));
        $authors = [];

        if($authorIds) {
            $authors = $entityManager
                ->getRepository(Author::class)
                ->findBy(array('id' => $authorIds));

            foreach($authors as $author) {
                $book->addAuthor($author);
            }
        }
    
        $entityManager->persist($book);
        $entityManager->flush();
    
        $data = [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'short_description' => $book->getShortDescription(),
            'image' => $book->getImage(),
            'date_published' => $book->getDatePublished(),
            'authors' => $authors,
        ];
            
        return $this->json($data);
    }

    // It's wrong but I don't see other way to deal with it. PHP doesn't know what is PATCH and 
    // how to work with it. This is sad(((
    #[Route('/{id}', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id, 
        EntityManagerInterface $entityManager, 
        BookUpdateRequest $request, 
        FileUploader $fileUploader,
    ): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
        $book->setName($request->request->get('name'), $book->getName());
        $book->setShortDescription($request->request->get('short_description', $book->getShortDescription()));
        
        $image = $request->files->get('image');
        if($image) {
            $fileName = $fileUploader->upload($image);
            $oldImage = $book->getImage();
            $book->setImage($fileName);

            $filesystem = new Filesystem();
            $filesystem->remove([$fileUploader->getBookImageDirectory() . '/' . $oldImage]);
        }

        $book->setDatePublished($request->request->get('date_published', $book->getDatePublished()));

        $authorIds = $request->request->get('authors');
        if($authorIds) {
            $authorIds = array_map('intval', explode(',', $authorIds));
            $authors = $entityManager
                ->getRepository(Author::class)
                ->findBy(array('id' => $authorIds));

            $book->syncAuthors(...$authors);
        }

        $authors = [];
        foreach($book->getAuthors() as $author) {
            $authors[] = [
                'id' => $author->getId(),
                'lastname' => $author->getFirstname(),
                'firstname' => $author->getLastname(),
                'secondaryname' => $author->getSecondaryname(),
            ];
        }

        $entityManager->persist($book);
        $entityManager->flush();
    
        $data =  [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'short_description' => $book->getShortDescription(),
            'image' => $book->getImage(),
            'date_published' => $book->getDatePublished(),
            'authors' => $authors,
        ];
            
        return $this->json($data);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(
        int $id, 
        EntityManagerInterface $entityManager, 
        FileUploader $fileUploader
    ): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }

        $imageToDelete = $book->getImage();
    
        $entityManager->remove($book);
        $entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove([$fileUploader->getBookImageDirectory() . '/' . $imageToDelete]);
    
        return $this->json('Successfully deleted a book with id ' . $id);
    }
}