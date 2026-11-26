<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation for adding and editing a hostel. */
class HostelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isWarden() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('hostels')->ignore($this->route('hostel'))],
            'location' => ['required', 'string', 'max:150'],
            'gender' => ['required', Rule::in(config('hostel.genders'))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Another hostel already has that name.',
        ];
    }
}
