<?php

namespace App\Helpers;

class ResponseHelper {
    public static function success(array $data = []) {
        return response()->json(['error' => false, ...$data]);
    }

    public static function error(string $message) {
        return response()->json(['error'=> true, 'message'=> $message]);
    }
}
