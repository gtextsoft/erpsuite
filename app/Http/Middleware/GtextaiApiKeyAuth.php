<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GtextaiApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => __('API key is missing')
            ], 401);
        }

        if ($apiKey !== env('GTEXTAI_X_API_KEY')) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => __('Invalid API key')
            ], 401);
        }

        return $next($request);
    }
}