<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin\AdminRole;
use App\Constants\AdminRoleConst;

class RoleDeleteGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request->validate([
            'target'    => 'required|numeric',
        ]);

        $role = AdminRole::find($request->target);
        if(!$role) return back()->with(['error' => ['Target role not found!']]); 
        if($role->name == AdminRoleConst::SUPER_ADMIN) {
            return back()->with(['error' => ['Super admin role can\'t deletable.']]);
        }
        return $next($request);
    }
}
