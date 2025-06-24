<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'permissions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the users that belong to this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(Users::class);
    }
    
    /**
     * Find a role by name.
     *
     * @param string $name
     * @return Roles|null
     */
    public static function findByName($name)
    {
        return self::where('name', $name)->first();
    }
    
    /**
     * Check if a role with the given name exists.
     *
     * @param string $name
     * @return bool
     */
    public static function nameExists($name)
    {
        return self::where('name', $name)->exists();
    }
}