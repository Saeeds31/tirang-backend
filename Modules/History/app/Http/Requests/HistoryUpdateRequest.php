<?php

namespace Modules\History\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistoryUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|min:3',
            'date' => 'sometimes|date',
            'description' => 'sometimes|string|min:3',
            'image' => 'sometimes|file|max:1024'
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
