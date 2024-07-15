<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class AuthorCreateRequest extends BaseRequest
{
    #[Assert\NotBlank([])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Firstname must be at least {{ limit }} characters long',
        maxMessage: 'Firstname cannot be longer than {{ limit }} characters',
    )]
    protected string $firstname;
    
    #[Assert\NotBlank([])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Lastname must be at least {{ limit }} characters long',
        maxMessage: 'Lastname cannot be longer than {{ limit }} characters',
    )]
    protected string $lastname;

    #[Assert\NotBlank([])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Secondaryname must be at least {{ limit }} characters long',
        maxMessage: 'Secondaryname cannot be longer than {{ limit }} characters',
    )]
    protected string $secondaryname;
}

