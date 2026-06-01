<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class ApiDocumentationService
{
    protected array $endpoints = [];
    protected array $controllers = [];

    public function generate(): array
    {
        $this->endpoints = [];
        $this->controllers = [];

        // Get all API routes
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            $uri = $route->uri;
            
            // Only include API routes
            if (!str_starts_with($uri, 'api/')) {
                continue;
            }

            // Skip health check and other utility routes
            if (str_contains($uri, 'up') || str_contains($uri, 'sanctum')) {
                continue;
            }

            $this->parseRoute($route);
        }

        // Group endpoints by category
        $grouped = $this->groupEndpoints();

        return [
            'info' => [
                'title' => 'Vinamilk E-Commerce API',
                'version' => '1.0.0',
                'description' => 'API documentation for Vinamilk E-Commerce platform',
            ],
            'endpoints' => $grouped,
            'statistics' => [
                'total' => count($this->endpoints),
                'public' => count(array_filter($this->endpoints, fn($e) => !$e['protected'])),
                'protected' => count(array_filter($this->endpoints, fn($e) => $e['protected'])),
                'by_method' => $this->countByMethod(),
            ],
        ];
    }

    protected function parseRoute($route): void
    {
        $methods = $route->methods;
        $uri = $route->uri;
        $action = $route->getAction();
        
        // Extract controller and method
        $controller = $action['controller'] ?? null;
        $controllerName = 'Unknown';
        $methodName = 'Unknown';
        
        if ($controller && is_string($controller)) {
            $parts = explode('@', $controller);
            $controllerClass = $parts[0] ?? null;
            $methodName = $parts[1] ?? 'Unknown';
            
            if ($controllerClass) {
                $controllerName = class_basename($controllerClass);
                $this->parseControllerDoc($controllerClass);
            }
        }

        // Check if route is protected
        $middleware = $route->middleware();
        $isProtected = in_array('auth:sanctum', $middleware) || in_array('auth', $middleware);

        // Clean URI
        $cleanUri = str_replace('api/v1/', '', $uri);
        if ($cleanUri === $uri) {
            $cleanUri = str_replace('api/', '', $uri);
        }

        // Extract parameters
        $parameters = $this->extractParameters($uri);

        $endpoint = [
            'method' => implode(', ', array_filter($methods, fn($m) => $m !== 'HEAD')),
            'uri' => $cleanUri,
            'full_uri' => $uri,
            'controller' => $controllerName,
            'action' => $methodName,
            'protected' => $isProtected,
            'parameters' => $parameters,
            'middleware' => $middleware,
            'description' => $this->getMethodDescription($controller, $methodName),
        ];

        $this->endpoints[] = $endpoint;
    }

    protected function extractParameters(string $uri): array
    {
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        return $matches[1] ?? [];
    }

    protected function parseControllerDoc(string $controllerClass): void
    {
        if (isset($this->controllers[$controllerClass])) {
            return;
        }

        try {
            $reflection = new ReflectionClass($controllerClass);
            $docComment = $reflection->getDocComment();
            
            $this->controllers[$controllerClass] = [
                'class' => class_basename($controllerClass),
                'namespace' => $reflection->getNamespaceName(),
                'description' => $this->parseDocComment($docComment),
                'methods' => [],
            ];

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isConstructor() || $method->isDestructor()) {
                    continue;
                }

                $methodDoc = $method->getDocComment();
                $this->controllers[$controllerClass]['methods'][$method->name] = [
                    'description' => $this->parseDocComment($methodDoc),
                    'parameters' => $this->extractMethodParameters($method),
                ];
            }
        } catch (\Exception $e) {
            $this->controllers[$controllerClass] = [
                'class' => class_basename($controllerClass),
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function parseDocComment(?string $docComment): string
    {
        if (!$docComment) {
            return '';
        }

        // Extract description from doc comment
        preg_match('/\/\*\*\s*(.*?)\s*\*\//s', $docComment, $matches);
        if (isset($matches[1])) {
            $description = preg_replace('/\s*\*\s*/', ' ', $matches[1]);
            $description = preg_replace('/@\w+.*$/m', '', $description);
            return trim($description);
        }

        return '';
    }

    protected function extractMethodParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $typeName = 'mixed';
            
            if ($type) {
                if (method_exists($type, 'getName')) {
                    $typeName = $type->getName();
                } elseif (method_exists($type, '__toString')) {
                    $typeName = (string) $type;
                }
            }
            
            $parameters[] = [
                'name' => $param->name,
                'type' => $typeName,
                'optional' => $param->isOptional(),
            ];
        }
        return $parameters;
    }

    protected function getMethodDescription(?string $controller, ?string $method): string
    {
        if (!$controller || !$method) {
            return '';
        }

        $parts = explode('@', $controller);
        $controllerClass = $parts[0] ?? null;

        if ($controllerClass && isset($this->controllers[$controllerClass]['methods'][$method])) {
            return $this->controllers[$controllerClass]['methods'][$method]['description'];
        }

        return '';
    }

    protected function groupEndpoints(): array
    {
        $groups = [
            'Authentication' => [],
            'Catalog & Products' => [],
            'Orders' => [],
            'User Management' => [],
            'Promotions & Vouchers' => [],
            'Care Subscription' => [],
            'Content' => [],
            'Search' => [],
            'Logistics' => [],
            'Chat' => [],
            'Admin' => [],
            'Other' => [],
        ];

        foreach ($this->endpoints as $endpoint) {
            $category = $this->categorizeEndpoint($endpoint);
            $groups[$category][] = $endpoint;
        }

        // Remove empty groups
        return array_filter($groups, fn($group) => !empty($group));
    }

    protected function categorizeEndpoint(array $endpoint): string
    {
        $uri = $endpoint['uri'];
        $controller = strtolower($endpoint['controller']);

        if (str_contains($uri, 'login') || str_contains($uri, 'register') || str_contains($uri, 'logout') || str_contains($uri, 'password')) {
            return 'Authentication';
        }

        if (str_contains($uri, 'catalog') || str_contains($uri, 'product') || str_contains($uri, 'collection') || str_contains($uri, 'home')) {
            return 'Catalog & Products';
        }

        if (str_contains($uri, 'order') || str_contains($uri, 'checkout') || str_contains($uri, 'cart')) {
            return 'Orders';
        }

        if (str_contains($uri, 'user') || str_contains($uri, 'address') || str_contains($uri, 'wishlist') || str_contains($uri, 'loyalty')) {
            return 'User Management';
        }

        if (str_contains($uri, 'promotion') || str_contains($uri, 'voucher') || str_contains($uri, 'reward')) {
            return 'Promotions & Vouchers';
        }

        if (str_contains($uri, 'care')) {
            return 'Care Subscription';
        }

        if (str_contains($uri, 'blog') || str_contains($uri, 'banner') || str_contains($uri, 'support')) {
            return 'Content';
        }

        if (str_contains($uri, 'search') || str_contains($uri, 'trending')) {
            return 'Search';
        }

        if (str_contains($uri, 'shipping') || str_contains($uri, 'store')) {
            return 'Logistics';
        }

        if (str_contains($uri, 'chat')) {
            return 'Chat';
        }

        if (str_contains($uri, 'admin')) {
            return 'Admin';
        }

        return 'Other';
    }

    protected function countByMethod(): array
    {
        $counts = [];
        foreach ($this->endpoints as $endpoint) {
            $methods = explode(', ', $endpoint['method']);
            foreach ($methods as $method) {
                $counts[$method] = ($counts[$method] ?? 0) + 1;
            }
        }
        return $counts;
    }
}
