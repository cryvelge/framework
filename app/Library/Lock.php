<?php

namespace App\Library;

use Cache;

class Lock
{
    /**
     * Executes code exclusively.
     *
     * @param string $name
     * @param int $timeout
     * @param callable $code
     * @return mixed
     * @throws
     */
    public static function lock(string $name, int $timeout = 5, callable $code)
    {
        if (Cache::lock($name, $timeout)->block($timeout)) {
            try {
                $ret = $code();
                Cache::lock($name)->release();
                return $ret;
            } catch (\Throwable $e) {
                Cache::lock($name)->release();
                throw $e;
            }
        } else {
            throw new \Exception('Lock time out');
        }
    }
}
