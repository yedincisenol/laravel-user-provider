<?php
namespace yedincisenol\UserProvider\Models;

use Illuminate\Database\Eloquent\Model;

class UserProvider extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'user_providers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'provider', 'access_token', 'refresh_token', 'expires_at', 'provider_user_id'
    ];


    /**
     * Get users of device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo();
    }
}