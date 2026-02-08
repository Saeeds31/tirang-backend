<?php

namespace Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|min:3',
            'description' => 'sometimes|string|min:3',
            "image" => 'sometimes|file|max:1024',
            "video" => 'sometimes|file|max:4096',
            "meta_title" => 'sometimes|string|min:3',
            "meta_description" => 'sometimes|string|min:3',
            "social_link" => 'sometimes|url|min:3',
            "website_link" => 'sometimes|url|min:3',
            'employer_id' => 'sometimes|integer|exist:employers,id',
            'category_id' => 'sometimes|integer|exist:portfolio_categories,id',
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
