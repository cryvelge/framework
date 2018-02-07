<?php

namespace App\Library;

class FrontendUrl
{
    public static function fakeNews($page, $params = [])
    {
        $config = config('frontend_url');
        $schema = $config['ssl'] ? 'https' : 'http';
        $host = $config['host'];
        $query = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        return "$schema://$host/news/?$query#/$page";
    }

    public static function sample(array $params = [])
    {
        $base = static::getBaseUrl();
        $query = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        return "$base?$query#/sample";
    }

    private static function getBaseUrl()
    {
        $config = config('frontend_url');
        $schema = $config['ssl'] ? 'https' : 'http';
        $host = $config['host'];
        return "$schema://$host/app/";
    }
}
