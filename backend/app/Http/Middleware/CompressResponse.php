<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompressResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if (strpos($request->header('Accept-Encoding'), 'gzip') !== false) {
            $response->headers->set('Content-Encoding', 'gzip');
            $response->setContent(gzencode($response->getContent(), 9));
        }

        return $response;
    }
}