<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    /**
     * Reverse geocode coordinates to get location name
     * Uses OpenStreetMap Nominatim API (free, no API key required)
     */
    public function reverseGeocode($latitude, $longitude)
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        // Cache key for this location
        $cacheKey = "geocode_{$latitude}_{$longitude}";
        
        // Check cache first (cache for 30 days as locations don't change)
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($latitude, $longitude) {
            try {
                // Use OpenStreetMap Nominatim API (free, no API key needed)
                $url = "https://nominatim.openstreetmap.org/reverse";
                
                $response = Http::timeout(5)
                    ->withHeaders([
                        'User-Agent' => 'Ofisi Attendance System/1.0',
                        'Accept' => 'application/json',
                    ])
                    ->get($url, [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'json',
                        'addressdetails' => 1,
                        'zoom' => 18, // More detailed address
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['address'])) {
                        $address = $data['address'];
                        
                        // Build location name from address components
                        $locationParts = [];
                        
                        // Try to get building/place name first
                        if (isset($address['name']) && !empty($address['name'])) {
                            $locationParts[] = $address['name'];
                        } elseif (isset($address['building']) && !empty($address['building'])) {
                            $locationParts[] = $address['building'];
                        }
                        
                        // Add road/street
                        if (isset($address['road']) && !empty($address['road'])) {
                            $locationParts[] = $address['road'];
                        }
                        
                        // Add suburb/neighborhood
                        if (isset($address['suburb']) && !empty($address['suburb'])) {
                            $locationParts[] = $address['suburb'];
                        } elseif (isset($address['neighbourhood']) && !empty($address['neighbourhood'])) {
                            $locationParts[] = $address['neighbourhood'];
                        }
                        
                        // Add city/town
                        if (isset($address['city']) && !empty($address['city'])) {
                            $locationParts[] = $address['city'];
                        } elseif (isset($address['town']) && !empty($address['town'])) {
                            $locationParts[] = $address['town'];
                        } elseif (isset($address['municipality']) && !empty($address['municipality'])) {
                            $locationParts[] = $address['municipality'];
                        }
                        
                        // Add state/region
                        if (isset($address['state']) && !empty($address['state'])) {
                            $locationParts[] = $address['state'];
                        } elseif (isset($address['region']) && !empty($address['region'])) {
                            $locationParts[] = $address['region'];
                        }
                        
                        // Build final location name
                        if (!empty($locationParts)) {
                            $locationName = implode(', ', $locationParts);
                            
                            // Limit length to 255 characters
                            if (strlen($locationName) > 255) {
                                $locationName = substr($locationName, 0, 252) . '...';
                            }
                            
                            return $locationName;
                        }
                        
                        // Fallback: use display_name if available
                        if (isset($data['display_name'])) {
                            $displayName = $data['display_name'];
                            if (strlen($displayName) > 255) {
                                $displayName = substr($displayName, 0, 252) . '...';
                            }
                            return $displayName;
                        }
                    }
                }
                
                // If API call failed or no address found, return formatted coordinates
                return "Lat: {$latitude}, Lng: {$longitude}";
                
            } catch (\Exception $e) {
                Log::warning('Reverse geocoding failed', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'error' => $e->getMessage()
                ]);
                
                // Return formatted coordinates as fallback
                return "Lat: {$latitude}, Lng: {$longitude}";
            }
        });
    }

    /**
     * Get location name with fallback options
     */
    public function getLocationName($latitude, $longitude, $fallbackLocation = null)
    {
        $locationName = $this->reverseGeocode($latitude, $longitude);
        
        // If reverse geocoding returns just coordinates and we have a fallback, use it
        if ($locationName && strpos($locationName, 'Lat:') === 0 && $fallbackLocation) {
            return $fallbackLocation;
        }
        
        return $locationName;
    }
}






