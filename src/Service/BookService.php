<?php
namespace App\Service;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use App\Requests\BookCreateRequest;
use App\Requests\BookUpdateRequest;

class BookService extends Service
{
    public function paginateAll(int $pageSize, int $page): Paginator
    {
        return $this->paginateEntities(Book::class, $pageSize, $page);
    }

    public function getOne(int $id): ?Book
    {
        return $this->entityManager->getRepository(Book::class)->find($id);
    }

    public function create(BookCreateRequest $request): Book
    {
        $book = new Book();
        $book->setName($request->request->get('name'));
        $book->setShortDescription($request->request->get('short_description', ''));

        $image = $request->files->get('image');
        if($image) {
            $fileName = $this->fileUploader->upload($image);
            $book->setImage($fileName);
        }

        $book->setDatePublished($request->request->get('date_published'));

        $authorIds = array_map('intval', explode(',', $request->request->get('authors')));
        $authors = [];

        if($authorIds) {
            $authors = $this->entityManager
                ->getRepository(Author::class)
                ->findBy(['id' => $authorIds]);

            foreach($authors as $author) {
                $book->addAuthor($author);
            }
        }
    
        $this->entityManager->persist($book);
        $this->entityManager->flush();
            
        return $book;
    }

    public function update(int $id, BookUpdateRequest $request): ?Book
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);

        if ($book) {
            $book->setName($request->request->get('name', $book->getName()));
            $book->setShortDescription(
                $request->request->get('short_description', $book->getShortDescription())
            );
            
            $image = $request->files->get('image');
            if($image) {
                $fileName = $this->fileUploader->upload($image);
                $oldImage = $book->getImage();
                $book->setImage($fileName);

                $this->removeImage($oldImage);
            }

            $book->setDatePublished(
                $request->request->get('date_published', $book->getDatePublished())
            );

            $authorIds = $request->request->get('authors');
            if($authorIds) {
                $authorIds = array_map('intval', explode(',', $authorIds));
                $authors = $this->entityManager
                    ->getRepository(Author::class)
                    ->findBy(['id' => $authorIds]);

                $book->syncAuthors(...$authors);
            }

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            $book = $this->entityManager->getRepository(Book::class)->find($id);
        }
        
            
        return $book;
    }

    public function delete(int $id): bool 
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return false;
        }

        $imageToDelete = $book->getImage();
    
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        $this->removeImage($imageToDelete);

        return true;
    }

    protected function removeImage(string $imageToDelete): void
    {
        $this->filesystem->remove([
            $this->fileUploader->getBookImageDirectory() . '/' . $imageToDelete,
        ]);
    }

    public function searchByAuthor(string $author, int $pageSize, int $page): array
    {
        $books = $this->entityManager
        ->getRepository(Book::class)
        ->findByAuthorAndPaginate($author, $page, $pageSize);
        
        $totalItems = $this->entityManager->getRepository(Book::class)->countdByAuthor($author);
    
        return [ 
            'books' => $books, 
            'count' => $totalItems, 
        ];
    }
}