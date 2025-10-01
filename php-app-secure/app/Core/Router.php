<?php

namespace App\Core;

/**
 * Simple router to register GET and POST routes and dispatch requests.
 */
class Router {
    /**
     * Registered routes by HTTP method.
     *
     * @var array{GET: array<string, string>, POST: array<string, string>}
     */
    protected $routes = ['GET'=>[], 'POST'=>[]];

    /**
     * Register a GET route.
     *
     * @param string $uri
     *   The URI path to match (e.g. '/home').
     * @param string $action
     *   Controller and method in "Controller@method" format.
     *
     * @return void
     */
    public function get($uri, $action) {
        $this->routes['GET'][$uri] = $action;
    }

    /**
     * Register a POST route.
     *
     * @param string $uri
     *   The URI path to match (e.g. '/home').
     * @param string $action
     *   Controller and method in "Controller@method" format.
     *
     * @return void
     */
    public function post($uri, $action) {
        $this->routes['POST'][$uri] = $action;
    }

    /**
     * Dispatch the incoming request.
     *
     * Parses the request URI, matches it against the registered routes
     * for the given HTTP method, and invokes the corresponding controller action.
     * If no route matches, sends a 404 response and exits.
     *
     * @param string $uri
     *   The full request URI, including query string.
     * @param string $method
     *   The HTTP method (e.g. 'GET' or 'POST').
     *
     * @return void
     */
    public function dispatch($uri, $method) {
        $path = parse_url($uri, PHP_URL_PATH);
        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo "404 — page not found";
            exit;
        }
        list($controller, $action) = explode('@', $this->routes[$method][$path]);
        $controller = "App\\Controllers\\{$controller}";
        (new $controller)->{$action}();
    }
}
