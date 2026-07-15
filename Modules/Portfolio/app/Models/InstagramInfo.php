<?php

namespace Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Portfolio\Database\Factories\InstagramInfoFactory;

class InstagramInfo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'portfolio_id',
        'like_count',
        'view_count',
        'reach_count',
        'follower_count',
        'mounth_count',
        'brand_logo',
        'insta_base_image',
        'first_image',
        'second_image',
        'third_image',
    ];
    protected $table = "instagram_info";
    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
