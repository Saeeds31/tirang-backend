<?php

namespace Modules\Users\Models;

use Illuminate\Support\Str;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Modules\Addresses\Models\Address;
use Modules\Register\Models\ImportantDocument;
use Modules\Register\Models\PhysicalCharacteristics;
use Modules\Wallet\Models\Wallet;
use Laravel\Sanctum\HasApiTokens;
use Modules\CourseOrder\Models\CourseOrder;
use Modules\Locations\Models\City;
use Modules\Receipt\Models\Receipt;
use Modules\Register\Models\IdentityDocument;
use Modules\Register\Models\Register;

// use Modules\Users\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'full_name',
        'mobile',
        'national_code',
        'birth_date',
        'image',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Get all addresses for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function getPermissionsAttribute()
    {
        return $this->roles
            ->map->permissions
            ->flatten()
            ->pluck('name')           
            ->unique()                
            ->values()                
            ->toArray();              
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->contains('name', $permission);
    }
}
