<?php

namespace App\Traits;

trait ErrorResponseTrait {

    /**
     * The method is used to send json error response, it can be customized through params
     *
     * @param string $message
     * @param integer $code
     * @return void
     */
    private function sendError($message = 'Error!', $code = 450)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}