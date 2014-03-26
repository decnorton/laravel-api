<?php namespace Dec\Api\Filters;

use \Auth;
use \Dec\Api\Exceptions\NotAuthorizedException;
use \Dec\Api\Models\Role;
use \Response;

class PermissionFilter {

    public function filter($route, $request, $permission)
    {
        if (Auth::guest() || !Auth::user()->can($permission))
        {
            throw new NotAuthorizedException("You don't have permission to do that!");
        }
    }

}