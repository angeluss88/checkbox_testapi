<?php 
namespace App\Response;

use OpenApi\Attributes as OA;
use App\Error\ValidationError;

class ValidationErrorResponse
{
    public string $message = "validation_failed";

    /** @var ValidationError[] */
    public array $errors;

    public function __construct(string $message, array $errors)
    {
        $this->message = $message;
        $this->errors = $errors;
    }
}