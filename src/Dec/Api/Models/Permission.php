<?php namespace Dec\Api\Models;

class Permission extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

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
     * Attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at'
    ];

    /**
     * Cast the ID to an integer
     * @return int
     */
    public function getIdAttribute()
    {
        return (int) $this->attributes['id'];
    }
}
