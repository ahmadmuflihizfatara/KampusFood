<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveMenuRequest extends FormRequest
{
    /**
     * Defense in depth: the route already requires the "admin" gate.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    /**
     * An unchecked checkbox sends nothing, so normalise it to false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['tersedia' => $this->boolean('tersedia')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'integer', 'min:500', 'max:1000000'],
            'tersedia' => ['boolean'],
        ];
    }
}
