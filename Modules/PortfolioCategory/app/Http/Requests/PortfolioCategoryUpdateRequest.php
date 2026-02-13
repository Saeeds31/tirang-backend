<?php

namespace Modules\PortfolioCategory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioCategoryUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title_fa' => 'sometimes|string|min:3',
            'title_en' => 'sometimes|string|min:3',
            'icon' => 'sometimes|file|max:1024',
            'meta_title' => 'nullable|string|min:3',
            'meta_description' => 'nullable|string|min:3',
            'show_in_home' => 'sometimes|boolean',
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
