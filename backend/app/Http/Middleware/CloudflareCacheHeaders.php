<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareCacheHeaders
{
    /**
     * Handle an incoming request and add appropriate cache headers for Cloudflare.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add cache headers for GET requests
        if ($request->method() !== 'GET') {
            // Bypass cache for non-GET requests
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        }

        // Bypass cache for authenticated requests
        if ($request->user() || $request->bearerToken() || $request->hasHeader('Authorization')) {
            $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        // Determine cache TTL based on route
        $path = $request->path();
        $cacheMaxAge = $this->getCacheMaxAge($path);

        if ($cacheMaxAge > 0) {
            // Set cache headers for Cloudflare
            $response->headers->set('Cache-Control', "public, max-age={$cacheMaxAge}, s-maxage={$cacheMaxAge}");
            
            // Add ETag for better cache validation (optional)
            if (!$response->headers->has('ETag')) {
                $etag = md5($response->getContent());
                $response->headers->set('ETag', $etag);
            }

            // Add Vary header if needed (for query parameters)
            if ($request->query->count() > 0) {
                $response->headers->set('Vary', 'Accept-Encoding');
            }
        } else {
            // No cache for specific routes
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    /**
     * Get cache max age in seconds based on route path.
     *
     * @param string $path
     * @return int Cache TTL in seconds (0 = no cache)
     */
    private function getCacheMaxAge(string $path): int
    {
        // Cache categories and settings for 24 hours
        if (str_starts_with($path, 'api/categories') || str_starts_with($path, 'api/settings')) {
            return 86400; // 24 hours
        }

        // Cache torrent detail pages for 4 hours
        if (str_starts_with($path, 'api/torrent/get/')) {
            return 14400; // 4 hours
        }

        // Cache home page and listings for 1 hour
        if (
            str_starts_with($path, 'api/torrents/home') ||
            str_starts_with($path, 'api/torrents/type/') ||
            str_starts_with($path, 'api/torrents/cat/') ||
            str_starts_with($path, 'api/torrents/catsub/') ||
            str_starts_with($path, 'api/torrents/search/') ||
            str_starts_with($path, 'api/library/')
        ) {
            return 3600; // 1 hour
        }

        // Cache torrent URLs and count for 30 minutes
        if (str_starts_with($path, 'api/torrents/urls') || str_starts_with($path, 'api/torrents/count')) {
            return 1800; // 30 minutes
        }

        // Cache option endpoints for 1 hour
        if (str_starts_with($path, 'api/option/')) {
            return 3600; // 1 hour
        }

        // Don't cache user-specific routes, login, or write operations
        if (
            str_starts_with($path, 'api/torrents/user/') ||
            str_starts_with($path, 'api/login') ||
            str_starts_with($path, 'api/user/') ||
            str_starts_with($path, 'api/my_uploads') ||
            str_starts_with($path, 'api/torrent/save') ||
            str_starts_with($path, 'api/torrent/create') ||
            str_starts_with($path, 'api/torrent/delete') ||
            str_starts_with($path, 'api/torrent/upload-image') ||
            str_starts_with($path, 'api/bing/')
        ) {
            return 0; // No cache
        }

        // Default: cache for 1 hour for other GET requests
        return 3600;
    }
}
