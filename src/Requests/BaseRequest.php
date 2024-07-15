<?php

namespace App\Requests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Response\ValidationErrorResponse;

abstract class BaseRequest
{
    public $request;
    public $files;

    public function __construct(protected ValidatorInterface $validator)
    {
        $this->request = Request::createFromGlobals()->request;
        $this->files = Request::createFromGlobals()->files;
        
        $this->populate();

        if ($this->autoValidateRequest()) {
            $this->validate();
        }
    }

    public function validate(): ConstraintViolationListInterface|bool
    {
        $errors = $this->validator->validate($this);

        $messages = [];
        /** @var \Symfony\Component\Validator\ConstraintViolation  */
        foreach ($errors as $message) {
            $messages[] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }

        /** @var ValidationErrorResponse $validationErrorResponse */
        $validationErrorResponse = new ValidationErrorResponse(
            'validation_failed',
            $messages
        );

        if (count($validationErrorResponse->errors) > 0) {
            $response = new JsonResponse($validationErrorResponse, 400);
            $response->send();

            return false;
        }

        return true;
    }

    protected function populate(): void
    {
        foreach ($this->request->all() as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
        foreach ($this->files->all() as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    // overwrite this method in child to disable auto-validation
    protected function autoValidateRequest(): bool
    {
        return true;
    }
}