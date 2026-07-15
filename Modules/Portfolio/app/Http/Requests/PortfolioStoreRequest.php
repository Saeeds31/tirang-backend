<?php

namespace Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3',
            'description' => 'nullable|string|min:3',
            "image" => 'required|file|max:1024',
            "video" => 'nullable|file|max:10240',
            "meta_title" => 'nullable|string|min:3',
            "meta_description" => 'nullable|string|min:3',
            "social_link" => 'nullable|url|max:255',
            "website_link" => 'nullable|url|max:255',
            'employer_id' => 'required|integer|exists:employers,id',
            'category_id' => 'required|integer|exists:portfolio_categories,id',
            'instagram_info' => 'nullable|array',
            'instagram_info.like_count' => 'nullable|integer|min:0',
            'instagram_info.view_count' => 'nullable|integer|min:0',
            'instagram_info.reach_count' => 'nullable|integer|min:0',
            'instagram_info.follower_count' => 'nullable|integer|min:0',
            'instagram_info.mounth_count' => 'nullable|integer|min:0',
            'instagram_info.brand_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'instagram_info.insta_base_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'instagram_info.first_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'instagram_info.second_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'instagram_info.third_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
