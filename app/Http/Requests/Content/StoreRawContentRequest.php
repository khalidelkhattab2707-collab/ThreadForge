<?php

namespace App\Http\Requests\Content;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRawContentRequest extends FormRequest
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
            'content'               => 'required|string|min:50|max:10000',
            'campaign_blueprint_id' => 'required|integer|exists:campaign_blueprints,id',
        ];
    }
}
