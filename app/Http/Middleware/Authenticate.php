<?php

namespace Wishginee\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @internal param null|string $guard
     */
    public function handle($request, Closure $next)
    {
        if(!app(Guard::class)->check()){
            return response('Unauthorized.', 401);
        }
        return $next($request);
    }
}
