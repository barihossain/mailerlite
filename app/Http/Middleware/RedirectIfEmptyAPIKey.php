<?php

namespace App\Http\Middleware;

use App\Models\Settings;
use Closure;
use Illuminate\Http\Request;

class RedirectIfEmptyAPIKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = '';
        $apiKeySetitng = Settings::where('key', 'MAILERLITE_API_KEY')->first();
        if (!is_null($apiKeySetitng)) {
            $apiKey = $apiKeySetitng->value;
        }

        if (!empty($apiKey)) {
            return $next($request);
        }

        return redirect()->route('home');
    }
}
