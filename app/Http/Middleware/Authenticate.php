<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {

        if (!$request->expectsJson()) {
            $result_array = array(
                'status' => 'fail',
                'status_code' => '401',
                'msg' => 'not Logged in'
            );
            print_r($result_array);
            exit;
            // return route('login');
        }
    }
}
