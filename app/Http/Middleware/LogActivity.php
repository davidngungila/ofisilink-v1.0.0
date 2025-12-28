<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only log authenticated requests
        if (!Auth::check()) {
            return $response;
        }
        
        $user = Auth::user();
        $method = $request->method();
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        
        // Skip logging for certain routes (like AJAX, API, etc.)
        $skipRoutes = [
            'activity-log.data',
            'activity-log.statistics',
            'activity-log.export',
        ];
        
        if ($routeName && in_array($routeName, $skipRoutes)) {
            return $response;
        }
        
        // Log based on HTTP method
        try {
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $action = $this->getActionFromMethod($method);
                $description = $this->getDescriptionFromRoute($routeName, $request);
                
                ActivityLogService::logAction(
                    $action,
                    $description,
                    null,
                    [
                        'route' => $routeName,
                        'method' => $method,
                        'url' => $request->fullUrl(),
                        'status_code' => $response->getStatusCode(),
                    ]
                );
            }
        } catch (\Exception $e) {
            // Don't fail the request if logging fails
            \Log::warning('Activity log middleware error: ' . $e->getMessage());
        }
        
        return $response;
    }
    
    private function getActionFromMethod(string $method): string
    {
        return match($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => 'viewed'
        };
    }
    
    private function getDescriptionFromRoute(?string $routeName, Request $request): string
    {
        if (!$routeName) {
            return "{$request->method()} {$request->path()}";
        }
        
        // Extract meaningful description from route name
        $parts = explode('.', $routeName);
        $action = end($parts);
        $module = count($parts) > 1 ? $parts[count($parts) - 2] : 'system';
        
        return ucfirst($action) . " in " . ucfirst(str_replace('-', ' ', $module));
    }
}






