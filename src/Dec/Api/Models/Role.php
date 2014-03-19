<?php namespace Dec\Api\Models;

class Role extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Attributes that are allowed to be mass assigned.
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];

    /**
     * Ardent validation rules
     * @var array
     */
    public static $rules = [
        'name'          => 'required|unique:roles|between:4,16',
        'display_name'  => 'required|unique:roles'
    ];

    /**
     * Attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = ['pivot', 'updated_at'];

    public function users()
    {
        return $this->belongsToMany('User', 'user_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany('Permission', 'role_permissions');
    }

    /**
     * Attach permission to current role
     *
     * @param $permission
     */
    public function attachPermission($permission)
    {
        if (is_object($permission))
            $permission = $permission->getKey();

        if (is_array($permission))
            $permission = $permission['id'];

        $this->permissions()->attach($permission);
    }

    /**
     * Attach multiple permissions to current role
     *
     * @param $permissions
     * @access public
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission)
        {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach permission form current role
     *
     * @param $permission
     */
    public function detachPermission($permission)
    {
        if (is_object($permission))
            $permission = $permission->getKey();

        if (is_array($permission))
            $permission = $permission['id'];

        $this->permissions()->detach($permission);
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param $permissions
     * @access public
     * @return void
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission)
        {
            $this->detachPermission($permission);
        }
    }

    /**
     * Alias for Eloquent ManyToMany::sync(). Overwrites existing.
     * @param  [type] $permissions [description]
     * @return [type]              [description]
     */
    public function syncPermissions($permissions)
    {
        if (!$permissions)
            $permissions = [];

        if (!is_array($permissions))
        {
            $this->errors()->add('permissions', 'Not an array');

            return false;
        }

        $sync = [];

        foreach($permissions as $permission)
        {
            if (is_object($permission))
                $id = $permission->getKey();

            if (is_array($permission))
                $id = $permission['id'];

            if (!empty($id))
                $sync[] = $id;
        }

        $this->permissions()->sync($sync);

        return true;
    }

}
