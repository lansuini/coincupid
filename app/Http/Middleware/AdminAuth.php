<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    protected $maps = [
        'GM' => [
            'Role' => '\App\Models\Manager\Role',
            'Admin' => '\App\Models\Manager\Admin',
            'LoginLog' => '\App\Models\Manager\LoginLog',
            'ActionLog' => '\App\Models\Manager\ActionLog',
        ],
        'ANALYSIS' => [
            'Role' => '\App\Models\Manager\Analysis\Role',
            'Admin' => '\App\Models\Manager\Analysis\Admin',
            'LoginLog' => '\App\Models\Manager\Analysis\LoginLog',
            'ActionLog' => '\App\Models\Manager\Analysis\ActionLog',
        ],
        'MERCHANT' => [
            'Role' => '\App\Models\Manager\Merchant\Role',
            'Admin' => '\App\Models\Manager\Merchant\Admin',
            'LoginLog' => '\App\Models\Manager\Merchant\LoginLog',
            'ActionLog' => '\App\Models\Manager\Merchant\ActionLog',
        ],
    ];

    public function handle(Request $request, Closure $next, $type)
    {   
       
        $model = new $this->maps[$type]['Admin']();
        $role = new $this->maps[$type]['Role']();
        // echo 1;exit;
        if (!$model->isLogin($request)) {
            
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                // return abort('403');
                return redirect('/login');
                
                // dd($request->session()->all());
            }
        }

        if (!$model->isPermission($request, $role)) {
            // return redirect('/login');
            // return 'Permission deneid';
            return response('Permission deneid.', 403);
        }

        // echo  $this->maps[$type]['Admin'];
        if (env('UNIQUE_LOGIN', false) && !$model->isUniqueLogin($request)) {
            // return view('GM/redirect', ['success' => false, 'message' => 'Your account is already logged in elsewhere', 'url' => '/loginout', 'waitTime' => 3]);
            $model->loginOut($request);
            return response('Your account is already logged in elsewhere. Please refresh current page!', 401); 
        }
        return $next($request);
    }
}
