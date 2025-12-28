<?php

use Illuminate\Support\Facades\Route;

// Helper function to get subdomain
function getSubdomain() {
    $host = request()->getHost();
    $parts = explode('.', $host);
    
    // For localhost or IP, return null
    if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
        return null;
    }
    
    // If more than 2 parts, first is subdomain
    if (count($parts) > 2) {
        return $parts[0];
    }
    
    return null;
}

/**
 * Minimal Landing Page Setup for ofisilink.com
 * 
 * This file serves ONLY the landing page on the main domain.
 * The full application is hosted on separate domains:
 * - live.ofisilink.com
 * - demo.ofisilink.com
 */

// Landing page - only on root domain
Route::get('/', function () {
    $subdomain = getSubdomain();
    
    // If on subdomain, redirect to the respective application domain
    if ($subdomain === 'live') {
        return redirect('https://live.ofisilink.com/login');
    }
    
    if ($subdomain === 'demo') {
        return redirect('https://demo.ofisilink.com/login');
    }
    
    // Show landing page only on root domain (ofisilink.com)
    return view('landing');
})->name('landing');

// Catch-all route - redirect any other paths to landing page
Route::fallback(function () {
    return redirect('/');
});

