<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // TODO: Use ENUMS later like you always do :)
        return [
            'type' => 'required|in:minecraft,steam,xbl',
            'username' => 'required_without:id',
            'id' => 'required_without:username',
        ];
    }
}
