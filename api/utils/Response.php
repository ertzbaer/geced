<?php
/**
 * API Response Utility
 * Standardized JSON response format
 */

namespace App\Utils;

class Response
{
    /**
     * Send success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     * @return void
     */
    public static function success($data = null, ?string $message = null, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => true
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send error response
     *
     * @param string $error
     * @param int $statusCode
     * @param array|null $details
     * @return void
     */
    public static function error(string $error, int $statusCode = 400, ?array $details = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error' => $error
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send validation error response
     *
     * @param array $errors
     * @return void
     */
    public static function validationError(array $errors): void
    {
        self::error('Validation failed', 422, $errors);
    }

    /**
     * Send unauthorized response
     *
     * @param string $message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden response
     *
     * @param string $message
     * @return void
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send not found response
     *
     * @param string $message
     * @return void
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send server error response
     *
     * @param string $message
     * @return void
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }

    /**
     * Send rate limit exceeded response
     *
     * @param string $message
     * @return void
     */
    public static function rateLimitExceeded(string $message = 'Too many requests'): void
    {
        self::error($message, 429);
    }
}
