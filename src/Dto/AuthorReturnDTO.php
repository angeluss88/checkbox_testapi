<?php
namespace App\Dto;

use App\Entity\Author;

class AuthorReturnDTO {

    public static function transform(Author $author, bool $addBooks = true): array 
    {
        $return = [
            'id' => $author->getId(),
            'lastname' => $author->getFirstname(),
            'firstname' => $author->getLastname(),
            'secondaryname' => $author->getSecondaryname(),
        ];

        if($addBooks) {
            $books = [];
            foreach($author->getBooks() as $book) {
                $books[] = BookReturnDTO::transform($book, false);
            } 
            $return['books'] = $books;
        }
        
        return $return;
    }
}