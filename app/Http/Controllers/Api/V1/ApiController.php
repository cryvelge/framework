<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;

class ApiController
{
    public function success($data = null, $extra = null)
    {
        $ret = [
            'status' => 0,
            'timestamp' => Carbon::now()->getTimestamp(),
        ];

        if (!is_null($data)) {
            $ret['data'] = $data;
        }

        if (!is_null($extra)) {
            $ret = array_merge($ret, $extra);
        }

        return response()->json($ret);
    }

    protected function fail($message, $status = -1, $extra = null)
    {
        $ret = [
            'status' => $status,
            'timestamp' => Carbon::now()->getTimestamp(),
            'message' => $message
        ];

        if (!is_null($extra)) {
            $ret = array_merge($ret, $extra);
        }

        return response()->json($ret);
    }

    protected function notFound($msg = null)
    {
        return response()->json([
            'status' => -1001,
            'message' => $msg ?? 'Not Found'
        ]);
    }

    protected function forbidden($msg = null)
    {
        return response()->json([
            'status' => -1002,
            'message' => $msg ?? 'Access Denied'
        ]);
    }
}
