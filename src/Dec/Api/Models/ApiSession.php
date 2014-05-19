<?php namespace Dec\Api\Models;

use Carbon\Carbon;

class ApiSession extends \Dec\Validation\Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_sessions';

    protected $hidden = [
        'public_key',
        'private_key'
    ];

    /**
     * Relationships
     */

    public function client()
    {
        return $this->hasOne('\Dec\Api\Models\ApiClient', 'id', 'client_id');
    }

    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    public function touchLastUsed()
    {
        $this->last_used = Carbon::now();
    }

}