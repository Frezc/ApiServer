<?php

namespace App\Http\Middleware;

use App\Exceptions\MsgException;
use App\Models\User;
use Closure;
use JWTAuth;

class UserAccess {
    /**
     * Use after jwt middleware.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $paramName
     * @return mixed
     * @throws MsgException
     */
    public function handle($request, Closure $next, $paramName = 'id') {
        $id = $request->route($paramName);
        if (!$id) $id = $request->input($paramName);

        $user = User::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        if ($self->id != $user->id && $self) {
            throw new MsgException('You have no access to this user.', 401);
        }

        return $next($request);
    }
}
