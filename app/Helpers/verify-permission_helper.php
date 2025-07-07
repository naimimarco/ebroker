<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('get_user_permissions')) {
    function get_user_permissions($id) {
        $userData = User::where('id', $id)->first();
        return $userData;
    }

}


if (!function_exists('has_permissions')) {
    function has_permissions($role, $module) {
        $role = trim($role);
        $module = trim($module);
        $id = Auth::id();;

        $general_system_permissions =  config('rolepermission');
        $userData = get_user_permissions($id);

        if (!empty($userData)) {

            if (intval($userData->type) != 0) {
                $permissions = json_decode($userData->permissions, 1);
                if (!empty($permissions)) {
                    if (array_key_exists($module, $general_system_permissions) && array_key_exists($module, $permissions)) {
                        if (array_key_exists($module, $permissions)) {
                            if (in_array($role, $general_system_permissions[$module])) {
                                if (!array_key_exists($role, $permissions[$module])) {
                                    return false; //User has no permission
                                }
                            }
                        }
                    } else {
                        return false; //User has no permission
                    }
                } else {
                    return false; //User has no permission
                }
            }
            return true; //User has permission
        }
    }

}
