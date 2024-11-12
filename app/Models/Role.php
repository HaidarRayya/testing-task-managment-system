<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
    /**
     *   get  not admin role
     * @param  Builder $query  
     * @return Builder query  
     */
    public function scopeNotAdminRole(Builder $query)
    {
        return $query->where('name', '!=', UserRole::ADMIN->value);
    }

    // get the if of a specific role

    public function scopeUserRole(Builder $query, $role)
    {
        return $query->where('name', '=', $role)->select('id');
    }

    /**
     *  search a role by name
     * @param  Builder $query  
     * @param  string $name  
     * @return Builder query  
     */
    public function scopeByName(Builder $query, $name)
    {
        if ($name != null)
            return $query->where('name', 'like', "%$name%");
        else
            return $query;
    }
}
