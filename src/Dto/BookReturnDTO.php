<?php
namespace App\Dto;

use App\Entity\Book;

class BookReturnDTO {

    public static function transform(Book $book, bool $addAuthors = true): array 
    {
        $return = [
            'id' => $book->getId(),
            'name' => $book->getName(),
            'short_description' => $book->getShortDescription(),
            'image' => $book->getImage(),
            'date_published' => $book->getDatePublished(),
        ];

        if($addAuthors) {
            $authors = [];
            foreach($book->getAuthors() as $author) {
                $return['authors'] = [];
                foreach($book->getAuthors() as $author) {
                    $return['authors'][] = AuthorReturnDTO::transform($author, false);
                }
            } 
        }
        
        return $return;
    }
}