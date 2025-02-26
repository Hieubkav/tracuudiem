<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\Permission;

class ThiTotNghiepManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ... $roles)
    {
        $coquyen = \Auth::user()->hasPermission(Permission::THITOTNGHIEP);
        if (!$coquyen) {
            foreach($roles as $role) {
                if(\Auth::user()->hasPermission($role)) {
                    $coquyen = true;
                    break;
                }
            }
            
            if(!$coquyen){
                abort(403);
            }
        }

        return $next($request);
    }
}
