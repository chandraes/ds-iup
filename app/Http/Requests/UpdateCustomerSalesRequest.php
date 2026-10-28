<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerSalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['su', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'karyawan_id' => ['required', 'integer', Rule::exists('karyawans', 'id')->where(function ($query): void {
                $query->where('status', 1)->whereIn('jabatan_id', fn ($roles) => $roles->select('id')->from('jabatans')->where('is_sales', 1));
            })],
            '_original_sales_id' => ['present', 'nullable', 'integer'],
            'sales_change_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
