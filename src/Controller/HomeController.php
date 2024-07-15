<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    #[Route('/')]
    public function main(): Response
    {
        return new Response(
            'Welcome to Checkbox_test_API. <BR />Go to <a href="https://localhost/api/doc">API Docs</a>'
        );
    }
}