<?php
namespace App\Dto;

use App\Entity\Book;

class BookWithoutAuthorsReturnDTO 
{
    public int $id;
    public string $name;
    public ?string $short_description;
    public string $image;
    public string $date_published;

    public static function transform(Book $book): BookWithoutAuthorsReturnDTO 
    {
        $return =  new self;
        $return->id = $book->getId();
        $return->name = $book->getName();
        $return->short_description = $book->getShortDescription();
        $return->image = $book->getImage();
        $return->date_published = $book->getDatePublished();
        
        return $return;
    }
}