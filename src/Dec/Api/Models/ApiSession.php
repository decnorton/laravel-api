<?php namespace Dec\Api\Models;

class ApiSession extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_sessions';

    public function client()
    {
        return $this->hasOne('ApiClient', 'id', 'client_id');
    }

}