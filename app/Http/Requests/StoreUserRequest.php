<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isAbleTo('user create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::user();

        if ($user->type === 'super admin') {
            return [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:100', 'unique:users,email'],
                'password' => ['nullable', 'string', 'min:6'],
                'mobile_no' => ['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9'],
                'workSpace_name' => ['nullable', 'string', 'max:191'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('created_by', creatorId())
                                 ->where('workspace_id', getActiveWorkSpace());
                }),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'mobile_no' => ['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9'],
            'roles' => ['required', 'exists:roles,id'],
        ];
    }

    /**
     * Get the validation rules for password when login is enabled.
     */
    public function withValidator($validator): void
    {
        $validator->sometimes('password', 'required|min:6', function ($input) {
            return $input->password_switch === 'on';
        });
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
            'password.min' => __('Password must be at least 6 characters.'),
            'mobile_no.regex' => __('Please enter a valid phone number.'),
        ];
    }
}
