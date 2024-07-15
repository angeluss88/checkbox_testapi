<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class BookCreateRequest extends BaseRequest
{
    #[Assert\NotBlank([])]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    protected $name;
    
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Short_description name must be at least {{ limit }} characters long',
        maxMessage: 'Short_description first name cannot be longer than {{ limit }} characters',
    )]
    protected $short_description;

    #[Assert\NotBlank([])]
    #[Assert\File(
        maxSize: '2M',
        extensions: ['jpg', 'jpeg', 'png'],
        extensionsMessage: 'Please upload a valid jpg or png',
    )]
    protected $image;

    #[Assert\NotBlank([])]
    #[Assert\Regex(
        pattern: '/^(?:19|20)\d{2}$/',
        message: 'Date_published should be a valid year',
    )]
    protected $date_published;

    #[Assert\NotBlank([])]
    #[Assert\Regex(
        pattern: '/^\s*-?\d+(?:,-?\d+)*\s*$/',
        message: 'Authors should be a list of ids like 1,2,3',
    )]
    protected $authors;
}

