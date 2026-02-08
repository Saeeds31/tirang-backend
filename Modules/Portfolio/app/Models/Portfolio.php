<?php

namespace Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Employer\Models\Employer;
use Modules\Image\Models\Image;
use Modules\PortfolioCategory\Models\PortfolioCategory;

// use Modules\Portfolio\Database\Factories\PortfolioFactory;

class Portfolio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        "image",
        "video",
        "meta_title",
        "meta_description",
        "social_link",
        "website_link",
        'employer_id',
        'category_id',
    ];
    public function category()
    {
        return $this->belongsTo(PortfolioCategory::class);
    }
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
    public function images()
    {
        return $this->belongsToMany(
            Image::class,
            'portfolio_images',
            'portfolio_id',
            'image_id'
        )->withTimestamps();
    }
}
