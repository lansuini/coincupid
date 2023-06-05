<?php
/**
 * Created by PhpStorm.
 * User: luobinhan
 * Date: 2022/10/5
 * Time: 19:29
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
class Language extends Middleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('language') AND in_array(Session::get('language'), Config::get('app.locales'))) {
            App::setLocale(Session::get('language'));
        }
        else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App::setLocale(Config::get('app.locale'));
        }
        return $next($request);
    }
}