<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response([
                'error' => 'unauthorized',
                'error_description' => 'Failed authentication.',
                'data' => [],
            ], 401);
        }

        $route_name = Route::currentRouteName();
        $route_name = explode('.', $route_name);
        if ($route_name[0] == env('ADMIN_PREFIX')) {
            return route('admin.login');
        }
        return route('admin.login');
    }
}
