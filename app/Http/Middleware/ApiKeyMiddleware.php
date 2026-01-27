<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Get the Authorization header
        $authorizationHeader = $request->header('Authorization');
        
        // Check if the header is in the correct format (Bearer X_API_KEY)
        if (!$authorizationHeader || !preg_match('/^Bearer (.+)$/', $authorizationHeader, $matches)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Extract the API key from the Authorization header
        $apiKey = $matches[1];

        // Get the original API key from the environment
        $originalKey = env('X_API_KEY');

        // Compare the API key from the header with the one in the environment
        if ($apiKey != $originalKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}