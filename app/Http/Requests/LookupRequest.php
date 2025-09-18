<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\GamingPlatform;
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
        return [
            'type' => ['required', 'in:'.GamingPlatform::implode(',')],
            'username' => 'required_without:id',
            'id' => 'required_without:username',
        ];
    }
}
