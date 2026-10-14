<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class LoggingContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Generate a unique request ID for each request
        $requestId = (string) Str::uuid();

        // Store the request ID on the request object
        $request->attributes->set('request_id', $requestId);

        return $next($request);
    }

}
