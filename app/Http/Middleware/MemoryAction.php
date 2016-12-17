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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @internal param string $table
     */
    public function handle($request, Closure $next)
    {
        // 将处理放在控制器后
        $response = $next($request);
        if ($response->getStatusCode() == 200) {
            // 请求成功，记录该请求
            $self = JWTAuth::parseToken()->authenticate();
            $data = [
                'ip' => $request->ip(),
                'user_id' => $self->id,
                'user_name' => $self->nickname,
                'method' => $request->method(),
                'path' => $request->path(),
                'params' => json_encode($request->all())
            ];
            Log::create($data);
        }
        return $response;
    }
}
