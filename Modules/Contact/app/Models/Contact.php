<?php

namespace Modules\Contact\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Contact\Database\Factories\ContactFactory;

class Contact extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'email',
        'address',
        'body',
    ];
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // scope برای پیام‌های دیده نشده
    public function scopeUnseen($query)
    {
        return $query->whereNull('seen_at');
    }

    // scope برای پیام‌های دیده شده
    public function scopeSeen($query)
    {
        return $query->whereNotNull('seen_at');
    }
}
