<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreatePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'min:5', 'max:100'],
            'options'        => ['required', 'array', 'min:2', 'max:4'],
            'options.*'      => ['required', 'string', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => 'A poll title is required.',
            'title.min'         => 'The poll title must be at least 5 characters.',
            'title.max'         => 'The poll title may not exceed 100 characters.',
            'options.required'  => 'At least two options are required.',
            'options.min'       => 'A poll must have at least 2 options.',
            'options.max'       => 'A poll may not have more than 4 options.',
            'options.*.required' => 'Each option must have a non-empty value.',
            'options.*.max'     => 'Each option may not exceed 50 characters.',
        ];
    }

    /**
     * Return validation errors as a JSON response with a 422 status.
     * Overrides the default redirect behaviour for API requests.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): never
    {
        throw new \Illuminate\Validation\ValidationException(
            $validator,
            response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422),
        );
    }
}
