<?php

namespace Modules\Team\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'full_name'     => ['required', 'string', 'max:255'],
            'post'     => ['required', 'string', 'max:255'],
            'image'     => ['required', 'file', 'max:1024'],
            'sort_order'     => ['required', 'integer'],
            'mobile'     => ['required', 'string', 'size:11'],
            'biography' => 'required|string|min:3',
            'show_in_home' => 'required|boolean'
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
