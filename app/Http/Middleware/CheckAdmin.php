<?php

/**
 * Authentication Middleware
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * CheckAdmin filters requests to verify the user is the administrator.
 */
class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next function in the flow.
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::user()->id !== 1):
            abort(404);
        endif;
        return $next($request);
    }
}
