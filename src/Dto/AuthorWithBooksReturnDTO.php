<?php
namespace App\Dto;

use App\Entity\Author;

class AuthorWithBooksReturnDTO 
{
    public int $id;
    public string $firstname;
    public string $lastname;
    public ?string $secondaryname;
    /** @var BookWithoutAuthorsReturnDTO[] */
    public array $books;


    public static function transform(Author $author): AuthorWithBooksReturnDTO 
    {
        $return = new self;
        $return->id = $author->getId();
        $return->lastname = $author->getFirstname();
        $return->firstname = $author->getLastname();
        $return->secondaryname = $author->getSecondaryname();

        $books = [];
        foreach($author->getBooks() as $book) {
            $return->books[] = BookWithoutAuthorsReturnDTO::transform($book);
        } 
        
        return $return;
    }
}