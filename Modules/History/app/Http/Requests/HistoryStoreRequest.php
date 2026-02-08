<?php

namespace Modules\History\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistoryStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3',
            'date' => 'required|date',
            'description' => 'required|string|min:3',
            'image' => 'required|file|max:1024'
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
