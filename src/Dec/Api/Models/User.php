<?php namespace Dec\Api\Models;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Model implements UserInterface, RemindableInterface {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Enable soft deletes
     *
     * @var  boolean
     */
    protected $softDelete = true;

    /**
     * Attributes that are allowed to be mass assigned.
     * @var array
     */
    protected $fillable = [];

    /**
     * Attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'password',
        'password_confirmation'
    ];

    /**
     * Ardent validation rules
     * @var array
     */
    public static $rules = [
        'username'              => 'required|unique:users|alpha_dash',
        'email'                 => 'required|unique:users|email',
        'password'              => 'required|between:4,20|confirmed',
        'password_confirmation' => 'between:4,20'
    ];

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    protected function buildUpdateRules(array $rules = array())
    {
        /**
         * Make sure an unchanged or empty password doesn't trigger validation
         */
        if (isset($rules['password']))
        {
            if($this->password == $this->getOriginal('password') || empty($this->password))
            {
                unset($rules['password']);
                unset($rules['password_confirmation']);
            }
        }

        return parent::buildUpdateRules($rules);
    }


    /**
     * Relations
     */

    public function roles()
    {
        return $this->belongsToMany('Role', 'user_roles');
    }

    public function sessions()
    {
        return $this->hasMany('Dec\Api\Models\ApiSession', 'user_id', 'id');
    }

    /**
     * Alias to eloquent many-to-many relation's
     * attach() method
     *
     * @param mixed $role
     *
     * @return void
     */
    public function attachRole($role)
    {
        if (is_object($role))
            $role = $role->getKey();

        if (is_array($role))
            $role = $role['id'];

        $this->roles()->attach($role);
    }

    /**
     * Attach multiple roles to a user
     *
     * @param $roles
     * @access public
     * @return void
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role)
        {
            $this->attachRole($role);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's
     * detach() method
     *
     * @param mixed $role
     *
     * @return void
     */
    public function detachRole($role)
    {
        if (is_object($role))
            $role = $role->getKey();

        if (is_array($role))
            $role = $role['id'];

        $this->roles()->detach($role);
    }

    /**
     * Detach multiple roles from a user
     *
     * @param $roles
     * @access public
     * @return void
     */
    public function detachRoles($roles)
    {
        foreach ($roles as $role)
        {
            $this->detachRole($role);
        }
    }

    /**
     * Checks if the user has a Role by its name
     *
     * @param string $name Role name.
     * @return boolean
     */
    public function hasRole($name)
    {
        foreach ($this->roles as $role) {
            if ($role->name == $name)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name
     *
     * @param string $permission Permission string.
     *
     * @access public
     *
     * @return boolean
     */
    public function can($permission)
    {
        foreach ($this->roles as $role) {
            // Validate against the Permission table
            foreach ($role->permissions as $perm)
            {
                if ($perm->name == $permission)
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function detachAllRoles()
    {
        $this->detachRoles($this->roles);
    }

    /**
     * Attributes
     */

    /**
     * Cast the ID to an integer
     *
     * @return int
     */
    public function getIdAttribute()
    {
        return (int) $this->attributes['id'];
    }

}
