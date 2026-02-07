<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAbleTo('user edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($userId) {
                    return $query->whereNotIn('id', [$userId])
                                 ->where('created_by', creatorId())
                                 ->where('workspace_id', getActiveWorkSpace());
                }),
            ],
            'mobile_no' => ['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9'],
            'roles' => ['nullable', 'exists:roles,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'email.required' => __('The email field is required.'),
            'email.unique' => __('This email is already taken.'),
            'mobile_no.regex' => __('Please enter a valid phone number.'),
        ];
    }
}
