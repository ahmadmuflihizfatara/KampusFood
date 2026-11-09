<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * TB-1 control: only menu_id and jumlah are accepted from the client.
 * Any total_harga sent by the frontend is not part of the rules, so it never reaches validated().
 */
class StoreOrderRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'integer', Rule::exists('menus', 'id')->where('tersedia', true)],
            'jumlah' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'menu_id.required' => 'Menu wajib dipilih.',
            'menu_id.integer' => 'Menu tidak valid.',
            'menu_id.exists' => 'Menu tidak dikenal atau sedang habis.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus bilangan bulat.',
            'jumlah.min' => 'Jumlah minimal 1 porsi.',
            'jumlah.max' => 'Jumlah maksimal 50 porsi per pesanan.',
        ];
    }
}
