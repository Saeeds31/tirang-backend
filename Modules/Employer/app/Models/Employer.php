<?php

namespace Modules\Employer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Employer\Database\Factories\EmployerFactory;

class Employer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'full_name',
        'mobile',
        'image',
        'business_label',
        'business_logo'
    ];
}
