<?php

namespace App\Http\Controllers;

use App\Models\User;

class FunctionController extends Controller
{
    /**
     * TODO: Store the Log activity
     * @return string
     */
    function log(string $level, string $user_id, string $method, string $resource, ...$args) {
        $logMessage = json_encode(['user_id' => $user_id, 'level' => $level, 'method' => $method, 'resource' => $resource, 'args' => $args]);
        if (!$logMessage)
            return 'log';
        return $logMessage;
    }

    /**
     * TODO: check whether the user has access permission or not.
     * This is a role based ACL
     */
    public function checkACL(string $level, string $user_id, string $resource, string $activity) {
        if (!$level || !$user_id) return false;
        // HARDCODE: define roles

        return true;
    }

    /**
     * get id clinic from user
     */
    public function getIDClinicUser(string $user_id) {
        $user = User::find($user_id);

        return $user->id_clinic;
    }
}
