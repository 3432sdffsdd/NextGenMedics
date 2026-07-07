<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $handler, $middleware);
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previous = $this->middleware;
        $this->middleware = array_merge($previous, $middleware);
        $callback($this, $prefix);
        $this->middleware = $previous;
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): self
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => array_merge($this->middleware, $middleware),
        ];
        return $this;
    }

    public function dispatch(Request $request, Container $container): void
    {
        $method = $request->method();
        $uri = $request->uri();
        $matchMethod = $method === 'HEAD' ? 'GET' : $method;

        if ($method === 'OPTIONS') {
            Response::json(['success' => true]);
            return;
        }

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] !== $matchMethod || !preg_match($pattern, $uri, $matches)) {
                continue;
            }

            $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
            $request->setParams($params);

            foreach ($route['middleware'] as $mw) {
                $instance = is_string($mw) ? $container->get($mw) : $mw;
                $result = $instance->handle($request);
                if ($result === false) {
                    return;
                }
            }

            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $methodName] = $handler;
                $controller = $container->get($class);
                $controller->$methodName($request);
            } else {
                $handler($request);
            }
            return;
        }

        Response::error('Route not found', 404);
    }
}
