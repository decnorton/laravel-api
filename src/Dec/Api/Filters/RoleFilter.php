<?php namespace Dec\Api\Filters;

use \Auth;
use \Dec\Api\Exceptions\NotAuthorizedException;
use \Dec\Api\Models\Role;
use \Response;

class RoleFilter {

    public function filter($route, $request, $role)
    {
        if (Auth::guest() || !Auth::user()->hasRole($role))
        {
            throw new NotAuthorizedException("You don't have permission to do that!");
        }
    }

}