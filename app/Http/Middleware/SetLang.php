<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLang
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (file_exists(storage_path('installed'))) {
            $locale = getActiveLanguage();
            App::setLocale($locale);
            
            // Also set the locale for Carbon dates if needed
            if (class_exists(\Carbon\Carbon::class)) {
                \Carbon\Carbon::setLocale($locale);
            }
        }

        return $next($request);
    }
}
