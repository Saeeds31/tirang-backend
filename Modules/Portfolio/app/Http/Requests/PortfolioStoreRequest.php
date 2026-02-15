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
            "video" => 'nullable|file|max:4096',
            "meta_title" => 'nullable|string|min:3',
            "meta_description" => 'nullable|string|min:3',
            "social_link" => 'nullable|url|max:255',
            "website_link" => 'nullable|url|max:255',
            'employer_id' => 'nullable|integer|exists:employers,id',
            'category_id' => 'required|integer|exists:portfolio_categories,id',
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
