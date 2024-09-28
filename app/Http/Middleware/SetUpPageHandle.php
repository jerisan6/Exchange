<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin\SetupPage;
use Illuminate\Support\Facades\URL;

class SetUpPageHandle
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
        $current_path   = $request->path();
        $current_route_name = $request->route()->getName();
        $setup_page    = SetupPage::where('status',false)->where('route_name',$current_route_name)->first();
        if($setup_page){
            $block_routes = $setup_page->block_routes ?? []; 
           
            if(in_array($current_route_name,$block_routes)){
                abort(404);
            }
        }
        
        
       
        return $next($request);
    }
}
