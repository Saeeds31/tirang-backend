<?php

namespace Modules\Employer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployerStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'full_name' => 'nullable|string|min:3',
            'mobile' => "required|string|size:11",
            'image' => 'nullable|file|max:1024',
            'business_label' => 'required|string|min:3',
            'business_logo' => 'required|file|max:1024'
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
