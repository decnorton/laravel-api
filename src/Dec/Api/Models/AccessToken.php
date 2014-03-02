<?php namespace Dec\Api\Models;

class AccessToken extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'api_access_tokens';

    public function client()
    {
        return $this->hasOne('ApiClient', 'id', 'client_id');
    }

}