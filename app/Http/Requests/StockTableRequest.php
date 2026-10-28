<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['su', 'admin', 'asisten-admin'], true);
    }

    public function rules(): array
    {
        return [
            'unit' => ['nullable', 'array', 'max:100'], 'unit.*' => ['required', 'integer', 'min:1', 'distinct'],
            'type' => ['nullable', 'integer', 'min:1'], 'kategori' => ['nullable', 'integer', 'min:1'],
            'barang_nama' => ['nullable', 'integer', 'min:1'], 'filter_harga' => ['nullable', 'boolean'],
            'mode' => ['nullable', Rule::in(['scroller', 'classic'])],
            'draw' => ['nullable', 'integer', 'between:0,2147483647'],
            'start' => ['nullable', 'integer', 'between:0,2147483647'], 'length' => ['nullable', 'integer', 'between:1,500'],
            'search' => ['nullable', 'array'], 'search.value' => ['nullable', 'string', 'max:100'],
        ];
    }
}
