<?php
/**
 * Simple Router Class
 * Routes API requests to appropriate handlers
 */

namespace App\Utils;

class Router
{
    private $routes = [];

    /**
     * Add GET route
     *
     * @param string $path
     * @param callable $handler
     * @return void
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add POST route
     *
     * @param string $path
     * @param callable $handler
     * @return void
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add PUT route
     *
     * @param string $path
     * @param callable $handler
     * @return void
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add PATCH route
     *
     * @param string $path
     * @param callable $handler
     * @return void
     */
    public function patch(string $path, callable $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Add DELETE route
     *
     * @param string $path
     * @param callable $handler
     * @return void
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add route to collection
     *
     * @param string $method
     * @param string $path
     * @param callable $handler
     * @return void
     */
    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Dispatch request to handler
     *
     * @return void
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove /api prefix if present
        $path = preg_replace('#^/api#', '', $path);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // No route found
        Response::notFound('Endpoint not found');
    }

    /**
     * Convert route path to regex pattern
     *
     * @param string $path
     * @return string
     */
    private function convertPathToRegex(string $path): string
    {
        // Convert :param to regex capture group
        $pattern = preg_replace('#:([a-zA-Z0-9_]+)#', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Get request body as JSON
     *
     * @return array
     */
    public static function getJsonBody(): array
    {
        $body = file_get_contents('php://input');
        $decoded = json_decode($body, true);
        return $decoded ?? [];
    }

    /**
     * Get query parameters
     *
     * @return array
     */
    public static function getQueryParams(): array
    {
        return $_GET;
    }
}
