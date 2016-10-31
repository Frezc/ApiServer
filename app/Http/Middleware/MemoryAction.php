<?php

namespace App\Http\Middleware;

use App\Models\Log;
use Closure;
use JWTAuth;

class MemoryAction
{
    /**
     * run after jwt auth.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $self = JWTAuth::parseToken()->authenticate();
        Log::create([
            'ip' => $request->ip(),
            'user_id' => $self->id,
            'method' => $request->method(),
            'url' => $request->url(),
            'params' => json_encode($request->all())
        ]);
        return $next($request);
    }
}
