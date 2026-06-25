<?php

namespace App\Http\Requests\Blueprint;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBlueprintRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:100',
            'target_audience'   => 'required|string|max:255',
            'tone'              => 'required|string|max:100',
            'max_length'        => 'integer|min:50|max:280',
            'max_hashtags'      => 'integer|min:0|max:5',
            'forbidden_words'   => 'array',
            'forbidden_words.*' => 'string|max:50',
        ];
    }
}
