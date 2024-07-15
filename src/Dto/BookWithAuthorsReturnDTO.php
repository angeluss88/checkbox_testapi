<?php
namespace App\Dto;

use App\Entity\Book;

class BookWithAuthorsReturnDTO 
{
    public int $id;
    public string $name;
    public ?string $short_description;
    public string $image;
    public string $date_published;
    /** @var AuthorWithoutBooksReturnDTO[] */
    public array $authors;

    public static function transform(Book $book): BookWithAuthorsReturnDTO 
    {
        $return =  new self;
        $return->id = $book->getId();
        $return->name = $book->getName();
        $return->short_description = $book->getShortDescription();
        $return->image = $book->getImage();
        $return->date_published = $book->getDatePublished();

        $authors = [];
        foreach($book->getAuthors() as $author) {
            $return->authors[] = AuthorWithoutBooksReturnDTO::transform($author);
        } 
        
        return $return;
    }
}