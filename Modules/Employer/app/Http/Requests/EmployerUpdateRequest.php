<?php

namespace Modules\Employer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployerUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'full_name' => 'sometimes|string|min:3',
            'mobile' => "sometimes|string|size:11",
            'image' => 'sometimes|file|max:1024',
            'business_label' => 'sometimes|string|min:3',
            'business_logo' => 'sometimes|file|max:1024'
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
