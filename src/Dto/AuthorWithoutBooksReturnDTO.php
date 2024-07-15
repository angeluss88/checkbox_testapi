<?php
namespace App\Dto;

use App\Entity\Author;

class AuthorWithoutBooksReturnDTO 
{
    public int $id;
    public string $firstname;
    public string $lastname;
    public ?string $secondaryname;

    public static function transform(Author $author): AuthorWithoutBooksReturnDTO 
    {
        $return = new self;
        $return->id = $author->getId();
        $return->lastname = $author->getFirstname();
        $return->firstname = $author->getLastname();
        $return->secondaryname = $author->getSecondaryname();
        
        return $return;
    }
}