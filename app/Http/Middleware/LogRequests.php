<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Log;
use Splunk;

class LogRequests
{
    public function handle(Request $request, \Closure $next)
    {
        $startTime = microtime(true);
        $response = $next($request);
        $finishTime = microtime(true);
        $url = $request->fullUrl();
        $method = $request->method();
        $statusCode = $response->getStatusCode();
        $time = (int)(($finishTime - $startTime) * 1000);
        Log::info("{$method} {$url} - {$statusCode} - {$time}");

        $route = $request->route();
        $data = [
            'method' => $method,
            'url' => $request->url(),
            'status' => $statusCode,
            'time' => $time,
        ];
        if (!is_null($route)) {
            $data['uri'] = $route->uri();
            $data['parameters'] = $route->parameters();
        }
        $user = session('user');
        if (!is_null($user)) {
            $data['user_id'] = $user->id;
        }
        Splunk::log('api_call', $data);

        return $response;
    }
}
