<?php

namespace App\Http\Middleware;

use App\Exceptions\MsgException;
use App\Role;
use Closure;
use JWTAuth;

class RoleCheck {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $roleName
     * @return mixed
     * @throws MsgException
     */
    public function handle($request, Closure $next, $roleName = 'user') {
        $self = JWTAuth::parseToken()->authenticate();
        $selfRole = Role::find($self->role_id);
        $targetRole = Role::where('name', $roleName)->first();
        if (!$selfRole || !$targetRole || $selfRole->mode < $targetRole->mode) {
            throw new MsgException('You have no access to this resource.', 401);
        }

        return $next($request);
    }
}
