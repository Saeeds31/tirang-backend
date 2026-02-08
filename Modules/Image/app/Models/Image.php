<?php

namespace Modules\Image\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Image\Database\Factories\ImageFactory;

class Image extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['path', 'alt'];

    // protected static function newFactory(): ImageFactory
    // {
    //     // return ImageFactory::new();
    // }
}
