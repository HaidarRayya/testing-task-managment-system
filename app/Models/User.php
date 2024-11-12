<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];
    protected $guarded = [
        'password',
        'role_id',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function role()
    {
        return $this->belongsTo(Role::class)->select('name', 'id');;
    }
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function taskStatusUpdate()
    {
        return $this->hasMany(TaskStatusUpdate::class);
    }

    /**
     *  search a user by name
     * @param  Builder $query  
     * @param  string $name  
     * @return Builder query  
     */
    public function scopeByUserName(Builder $query, $name)
    {
        if ($name != null)
            return $query->where('name', 'like', "%$name%");
        else
            return $query;
    }
}