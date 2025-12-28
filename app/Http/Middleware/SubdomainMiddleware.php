<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubdomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $subdomain): Response
    {
        $host = $request->getHost();
        $subdomainFromHost = $this->extractSubdomain($host);
        
        // Check if the request matches the expected subdomain
        if ($subdomainFromHost !== $subdomain) {
            // If expecting root domain but got subdomain, redirect to appropriate subdomain
            if ($subdomain === 'root' && $subdomainFromHost !== null) {
                // Redirect subdomain to root
                $url = $request->scheme() . '://' . $this->getRootDomain($host) . $request->getRequestUri();
                return redirect($url, 301);
            }
            
            // If expecting subdomain but got root, redirect to subdomain
            if ($subdomain !== 'root' && $subdomainFromHost === null) {
                $url = $request->scheme() . '://' . $subdomain . '.' . $this->getRootDomain($host) . $request->getRequestUri();
                return redirect($url, 301);
            }
        }
        
        return $next($request);
    }
    
    /**
     * Extract subdomain from host
     */
    private function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);
        
        // If we have more than 2 parts, the first part is likely a subdomain
        // For example: live.ofisilink.com -> ['live', 'ofisilink', 'com']
        if (count($parts) > 2) {
            return $parts[0];
        }
        
        // For localhost or IP addresses, return null
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        return null;
    }
    
    /**
     * Get root domain from host
     */
    private function getRootDomain(string $host): string
    {
        $parts = explode('.', $host);
        
        // If we have more than 2 parts, return the last 2 parts
        if (count($parts) > 2) {
            return implode('.', array_slice($parts, -2));
        }
        
        return $host;
    }
}

