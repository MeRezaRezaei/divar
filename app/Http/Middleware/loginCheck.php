<?php

namespace App\Http\Middleware;

use Closure;

class loginCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->session()->has('user_id')){
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'msg'    => 'you are not loged in, login needed for calling this end point!'
        ]);
    }
}
