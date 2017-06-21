<?php

namespace App\Http\Middleware;

use App\Components\User\Models\User;
use Closure;
use EasyWeChat\Foundation\Application;

class Auth
{
    public function handle($request, Closure $next)
    {
        if (env('APP_ENV') == 'debug') {
            return $this->fakeAuth($request, $next);
        } else {
            return $this->realAuth($request, $next);
        }
    }

    private function fakeAuth($request, Closure $next)
    {
        $user = User::find(env('FAKE_AUTH_USER', '552fde8708a35f8c79089d40'));
        session(['user' => $user]);
        return $next($request);
    }

    private function realAuth($request, Closure $next)
    {
        $user = session('user');
        if(is_null($user)) {
            return response()->json([
                'status' => 1,
                'message' => 'Login needed',
            ]);
        } else {
            return $next($request);
        }
    }
}
