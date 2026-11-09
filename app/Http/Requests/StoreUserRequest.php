<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Defense in depth: the route already requires the "admin" gate.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash:ascii', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in(['mahasiswa', 'admin'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah dipakai.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, - dan _.',
            'email.unique' => 'Email sudah dipakai.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.in' => 'Role tidak valid.',
        ];
    }
}
