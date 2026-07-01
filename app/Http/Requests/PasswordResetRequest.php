<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\PasswordValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetRequest extends FormRequest{
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Passwords do not match',
        ];
    }


     public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $password = $this->input('password');
            
            if ($password) {
                $validation = PasswordValidator::validate($password);
                
                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $error) {
                        $validator->errors()->add('password', $error);
                    }
                }

                if (PasswordValidator::isCommonPassword($password)) {
                    $validator->errors()->add('password', 'This password is too common. Please choose a stronger password.');
                }
            }
        });
    }


    public function sendResetEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? back()->with('success', __($status))
        : back()->withErrors(['email' => __($status)]);
}


}
