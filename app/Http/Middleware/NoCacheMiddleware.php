<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevents browser caching of authenticated pages.
 * Stops the browser back-button from showing protected content after logout.
 *
 * Uses $response->headers->set() instead of ->withHeaders() because
 * withHeaders() does not exist on Symfony StreamedResponse (file downloads)
 * or BinaryFileResponse, causing 500 errors on those routes.
 */
class NoCacheMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Use Symfony-compatible header API — works on ALL response types:
        // Response, JsonResponse, StreamedResponse, BinaryFileResponse, RedirectResponse
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
