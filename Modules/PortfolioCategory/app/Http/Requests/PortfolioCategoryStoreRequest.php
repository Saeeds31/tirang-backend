<?php

namespace Modules\PortfolioCategory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioCategoryStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title_fa'=>'required|string|min:3',
            'title_en'=>'required|string|min:3',
            'icon'=>'required|file|max:1024',
            'meta_title'=>'nullable|string|min:3',
            'meta_description'=>'nullable|string|min:3',
            'show_in_home'=>'required|boolean',
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
