<?php

namespace Modules\PortfolioCategory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PortfolioCategory\Database\Factories\PortfolioCategoryFactory;

class PortfolioCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title_fa',
        'title_en',
        'icon',
        'meta_title',
        'meta_description',
        'show_in_home',
    ];

    protected $table = "portfolio_categories";
    protected $casts = [
        'show_in_home' => 'boolean',
    ];
}
