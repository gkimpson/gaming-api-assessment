<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\GamingPlatform;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

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

    public function messages(): array
    {
        return [
            'type.in' => 'The selected platform type is invalid. Supported platforms are: '.GamingPlatform::implode().'.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
