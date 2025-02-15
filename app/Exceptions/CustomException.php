<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CustomException extends Exception
{
    protected string $errorKey;
    protected array $errorData;

    /**
     * CustomException constructor.
     *
     * @param string $errorKey
     * @param array $errorData
     * @param int $statusCode
     */
    public function __construct(string $errorKey, array $errorData = [], int $statusCode = 400)
    {
        parent::__construct($errorKey, $statusCode);
        $this->errorKey = $errorKey;
        $this->errorData = $errorData;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        $messages = config('error_messages');

        // Fetch error message from config, fallback to default
        $message = $messages[$this->errorKey] ?? 'An unexpected error occurred.';

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $this->errorData,
        ], $this->getCode());
    }
}
