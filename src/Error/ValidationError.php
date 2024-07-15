<?php
namespace App\Error;

use OpenApi\Attributes as OA;

class ValidationError
{
    #[OA\Property(property: "property", type: "string")]
    public string $property;

    #[OA\Property(property: "value", type: "string")]
    public string $value;

    #[OA\Property(property: "message", type: "string")]
    public string $message;
}