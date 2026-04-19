<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'email'        => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^(\+?256|0)[7][0-9]{8}$/'],
            'student_id'   => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex'         => 'Name may only contain letters, spaces, hyphens, and apostrophes.',
            'name.min'           => 'Name must be at least 2 characters.',
            'email.email'        => 'Please enter a valid email address with a real domain.',
            'phone_number.regex' => 'Enter a valid Ugandan phone number (e.g., 0771234567 or +256771234567).',
        ];
    }
}
