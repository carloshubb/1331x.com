# Cloudflare CDN Setup Guide

This guide explains how to set up Cloudflare as a CDN for your Laravel backend to reduce server load and improve response times.

## Table of Contents
1. [Overview](#overview)
2. [Step 1: Cloudflare Account Setup](#step-1-cloudflare-account-setup)
3. [Step 2: DNS Configuration](#step-2-dns-configuration)
4. [Step 3: SSL/TLS Settings](#step-3-ssltls-settings)
5. [Step 4: Cache Configuration](#step-4-cache-configuration)
6. [Step 5: Page Rules (Cache Rules)](#step-5-page-rules-cache-rules)
7. [Step 6: Laravel Backend Configuration](#step-6-laravel-backend-configuration)
7. [Step 7: Testing](#step-7-testing)
8. [Step 8: Monitoring](#step-8-monitoring)

---

## Overview

Cloudflare CDN will:
- **Cache static and dynamic content** at edge locations worldwide
- **Reduce server load** by serving cached responses
- **Improve response times** for users globally
- **Provide DDoS protection** and security features
- **Compress responses** automatically

### What Gets Cached
- ✅ GET requests to API endpoints (with proper cache headers)
- ✅ Static assets (images, fonts, CSS, JS)
- ✅ Public torrent listings and detail pages

### What Should NOT Be Cached
- ❌ POST/PUT/DELETE requests
- ❌ Authenticated user data
- ❌ Real-time data that changes frequently
- ❌ Admin endpoints

---

## Step 1: Cloudflare Account Setup

1. **Sign up/Login** to [Cloudflare](https://dash.cloudflare.com/)
2. **Add your domain**:
   - Click "Add a Site"
   - Enter your domain (e.g., `yourdomain.com`)
   - Select a plan (Free plan works for most use cases)

---

## Step 2: DNS Configuration

1. **Change Nameservers**:
   - Cloudflare will provide you with nameservers (e.g., `alice.ns.cloudflare.com`)
   - Update your domain registrar with these nameservers
   - Wait for DNS propagation (can take 24-48 hours, usually faster)

2. **Add DNS Records**:
   - **A Record**: Point your domain/subdomain to your backend server IP
     ```
     Type: A
     Name: api (or @ for root)
     IPv4: YOUR_SERVER_IP
     Proxy: ✅ Proxied (orange cloud)
     ```
   - **CNAME Record** (if using subdomain):
     ```
     Type: CNAME
     Name: api
     Target: yourdomain.com
     Proxy: ✅ Proxied
     ```

   **Important**: Make sure the proxy status is **ON** (orange cloud) for CDN to work.

---

## Step 3: SSL/TLS Settings

1. Go to **SSL/TLS** → **Overview**
2. Set encryption mode to **Full (strict)**:
   - This ensures end-to-end encryption
   - Requires valid SSL certificate on your backend server

3. **SSL/TLS** → **Edge Certificates**:
   - Enable "Always Use HTTPS"
   - Enable "Automatic HTTPS Rewrites"
   - Enable "Minimum TLS Version" (1.2 or higher)

---

## Step 4: Cache Configuration

### 4.1 Caching Level

1. Go to **Caching** → **Configuration**
2. Set **Caching Level** to **Standard**
3. Set **Browser Cache TTL** to **Respect Existing Headers** (we'll set headers in Laravel)

### 4.2 Cache Rules (Recommended)

Go to **Caching** → **Cache Rules** and create rules:

#### Rule 1: Cache Public API GET Requests
```
Rule Name: Cache Public API GET Requests
When: 
  - Request Method equals GET
  - AND URI Path starts with /api/torrents/
  - AND URI Path does not start with /api/torrents/user/
Cache Status: Eligible for Cache
Edge Cache TTL: 1 hour
Browser Cache TTL: Respect Existing Headers
```

#### Rule 2: Cache Torrent Details
```
Rule Name: Cache Torrent Details
When:
  - Request Method equals GET
  - AND URI Path matches /api/torrent/get/*
Cache Status: Eligible for Cache
Edge Cache TTL: 4 hours
Browser Cache TTL: Respect Existing Headers
```

#### Rule 3: Cache Categories and Settings
```
Rule Name: Cache Categories and Settings
When:
  - Request Method equals GET
  - AND URI Path matches /api/categories OR /api/settings/*
Cache Status: Eligible for Cache
Edge Cache TTL: 24 hours
Browser Cache TTL: Respect Existing Headers
```

#### Rule 4: Bypass Cache for Authenticated Requests
```
Rule Name: Bypass Authenticated Requests
When:
  - Request Header Authorization exists
  - OR Request Header Cookie contains sanctum_token
Cache Status: Bypass
```

#### Rule 5: Bypass Cache for POST/PUT/DELETE
```
Rule Name: Bypass Write Operations
When:
  - Request Method is one of POST, PUT, DELETE, PATCH
Cache Status: Bypass
```

---

## Step 5: Page Rules (Legacy - Use Cache Rules Instead)

If you prefer Page Rules (older method):

1. Go to **Rules** → **Page Rules**
2. Create rules with these settings:

**Rule 1: Cache API GET Requests**
- URL Pattern: `*yourdomain.com/api/torrents/*`
- Settings:
  - Cache Level: Standard
  - Edge Cache TTL: 1 hour
  - Browser Cache TTL: Respect Existing Headers

**Rule 2: Cache Torrent Details**
- URL Pattern: `*yourdomain.com/api/torrent/get/*`
- Settings:
  - Cache Level: Standard
  - Edge Cache TTL: 4 hours

**Rule 3: Bypass Auth Endpoints**
- URL Pattern: `*yourdomain.com/api/login` OR `*yourdomain.com/api/*/save`
- Settings:
  - Cache Level: Bypass

---

## Step 6: Laravel Backend Configuration

### 6.1 Install Cloudflare Trusted Proxy Package

```bash
cd backend
composer require monicahq/cloudflare
```

### 6.2 Configure Trusted Proxies

Update `backend/app/Http/Middleware/TrustProxies.php` (or create if it doesn't exist):

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*'; // Trust all proxies (Cloudflare)

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
```

Register it in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    // Trust proxies first (for Cloudflare)
    $middleware->append(\App\Http\Middleware\TrustProxies::class);
    // Add cache headers for Cloudflare CDN
    $middleware->append(\App\Http\Middleware\CloudflareCacheHeaders::class);
    // Compress responses
    $middleware->append(\App\Http\Middleware\CompressResponse::class);
})
```

**Note**: The middleware has already been registered in your `bootstrap/app.php` file.

### 6.3 Cache Headers Middleware

A middleware has been created at `backend/app/Http/Middleware/CloudflareCacheHeaders.php` that will:
- Add appropriate cache headers for GET requests
- Bypass cache for authenticated requests
- Set different TTLs for different endpoints:
  - Categories/Settings: 24 hours
  - Torrent details: 4 hours
  - Home/listings: 1 hour
  - Torrent URLs/count: 30 minutes
  - User-specific routes: No cache

### 6.4 Update Controllers to Use Cache Headers

The middleware will automatically add cache headers, but you can also add them manually in controllers:

```php
// Example in TorrentController
public function getHomeData(Request $request)
{
    $data = // ... your logic
    
    return response()->json($data)
        ->header('Cache-Control', 'public, max-age=3600') // 1 hour
        ->header('X-Cache-Status', 'MISS'); // For debugging
}
```

---

## Step 7: Testing

### 7.1 Test Cache Headers

```bash
# Test a GET request
curl -I https://yourdomain.com/api/torrents/home

# Look for these headers:
# CF-Cache-Status: HIT (if cached)
# Cache-Control: public, max-age=3600
```

### 7.2 Test from Different Locations

Use tools like:
- [WebPageTest](https://www.webpagetest.org/)
- [GTmetrix](https://gtmetrix.com/)
- Cloudflare Analytics dashboard

### 7.3 Verify Cache is Working

1. Make a request to a cached endpoint
2. Check response headers for `CF-Cache-Status: HIT`
3. Make the same request again - should be faster
4. Check Cloudflare dashboard → Analytics → Caching

---

## Step 8: Monitoring

### 8.1 Cloudflare Analytics

Monitor in Cloudflare Dashboard:
- **Analytics** → **Caching**: Cache hit ratio (aim for >80%)
- **Analytics** → **Performance**: Response times
- **Analytics** → **Traffic**: Bandwidth savings

### 8.2 Laravel Logging

Add logging to track cache hits/misses:

```php
Log::info('API Request', [
    'url' => $request->fullUrl(),
    'cf_cache_status' => $request->header('CF-Cache-Status'),
    'cf_ray' => $request->header('CF-RAY'),
]);
```

### 8.3 Key Metrics to Monitor

- **Cache Hit Ratio**: Should be >80% for cached endpoints
- **Bandwidth Savings**: Shows how much traffic is served from cache
- **Response Time**: Should decrease after caching is active
- **Origin Requests**: Should decrease significantly

---

## Advanced Configuration

### Purge Cache

When content is updated, purge Cloudflare cache:

**Via Cloudflare Dashboard:**
1. Go to **Caching** → **Configuration** → **Purge Cache**
2. Select "Purge Everything" or "Custom Purge"

**Via API:**
```php
// Add to your update/delete methods
use Illuminate\Support\Facades\Http;

Http::withHeaders([
    'X-Auth-Email' => env('CLOUDFLARE_EMAIL'),
    'X-Auth-Key' => env('CLOUDFLARE_API_KEY'),
])
->delete("https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache", [
    'files' => [
        'https://yourdomain.com/api/torrent/get/123/slug'
    ]
]);
```

### Custom Cache Keys

You can customize cache keys based on query parameters:

```php
// In middleware or controller
$cacheKey = $request->fullUrl(); // Includes query params
```

### Compression

Cloudflare automatically compresses responses. Your existing `CompressResponse` middleware is still active, which means:
- **Double compression may occur**: Cloudflare compresses, then Laravel compresses again
- **Recommendation**: Consider disabling Laravel compression when behind Cloudflare, or modify it to check for Cloudflare headers:
  ```php
  // In CompressResponse middleware
  if ($request->hasHeader('CF-Ray')) {
      // Skip compression - Cloudflare handles it
      return $next($request);
  }
  ```
- Alternatively, you can keep both - Cloudflare will detect already-compressed content and skip re-compression

---

## Troubleshooting

### Cache Not Working

1. **Check Proxy Status**: Ensure DNS records are proxied (orange cloud)
2. **Check Cache Rules**: Verify rules are active and matching correctly
3. **Check Headers**: Ensure responses have cacheable headers
4. **Check SSL**: Ensure SSL/TLS is properly configured

### Too Much Caching

1. Review cache rules - may be caching dynamic content
2. Add more bypass rules for authenticated endpoints
3. Reduce cache TTL for frequently updated content

### Origin Server Still Getting Requests

1. Check cache hit ratio in analytics
2. Verify cache rules are matching requests
3. Check if responses have `no-cache` or `private` headers
4. Ensure query parameters aren't causing cache misses

---

## Best Practices

1. **Cache Static Content Longer**: Images, fonts, CSS/JS can be cached for weeks
2. **Cache Dynamic Content Shorter**: API responses should have shorter TTLs (1-4 hours)
3. **Purge on Updates**: Clear cache when content is modified
4. **Monitor Hit Ratio**: Aim for >80% cache hit ratio
5. **Use Cache Tags**: If using Cloudflare Enterprise, use cache tags for selective purging
6. **Test Thoroughly**: Test cache behavior in staging before production

---

## Cost Considerations

- **Free Plan**: Includes CDN, basic caching, DDoS protection
- **Pro Plan ($20/month)**: Better caching, more page rules, image optimization
- **Business Plan ($200/month)**: Advanced caching, custom cache rules, better analytics
- **Enterprise**: Custom pricing, advanced features

For most use cases, the **Free plan** is sufficient to start.

---

## Additional Resources

- [Cloudflare Cache Rules Documentation](https://developers.cloudflare.com/cache/how-to/cache-rules/)
- [Cloudflare API Documentation](https://developers.cloudflare.com/api/)
- [Laravel Trusted Proxies](https://laravel.com/docs/requests#configuring-trusted-proxies)

---

## Summary

After completing these steps:
1. ✅ Your domain is proxied through Cloudflare
2. ✅ SSL/TLS is configured
3. ✅ Cache rules are set up
4. ✅ Laravel is configured to work with Cloudflare
5. ✅ Cache headers are properly set
6. ✅ Monitoring is in place

Your backend should now experience:
- **Reduced server load** (60-80% of requests served from cache)
- **Faster response times** (especially for global users)
- **Better security** (DDoS protection, WAF)
- **Lower bandwidth costs**
