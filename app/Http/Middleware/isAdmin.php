<?php

namespace App\Http\Middleware;

use Closure;

class isAdmin
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

            if($request->session()->get('role') == 2)
            return $next($request)
            ;
        }
        else{
            return response()->json([
                'status' => false,
                'msg'    => 'you are not loged in, login needed for calling this end point!'
            ]);
        }

        return response()->json([
            'status' => false,
            'msg'    => 'only admins can access this endpoint.',
        ]);
    }
}
