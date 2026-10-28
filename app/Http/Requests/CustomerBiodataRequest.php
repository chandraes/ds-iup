<?php

namespace App\Http\Requests;

use App\Models\db\Konsumen;
use App\Models\Wilayah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['su', 'admin'], true);
    }

    public function rules(): array
    {
        $customer = $this->route('konsumen');
        $province = is_scalar($this->input('provinsi_id')) ? Wilayah::find($this->input('provinsi_id')) : null;
        $city = is_scalar($this->input('kabupaten_kota_id')) ? Wilayah::find($this->input('kabupaten_kota_id')) : null;
        $editing = $customer instanceof Konsumen;

        return [
            '_original_sales_id' => $editing ? ['present', 'nullable', 'integer'] : ['exclude'],
            'sales_change_reason' => $editing ? ['nullable', 'string', 'max:500'] : ['exclude'],
            'kode_toko_id' => ['required', 'integer', 'exists:kode_tokos,id'],
            'nama' => ['required', 'string', 'max:255'], 'cp' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:40', ...($editing && $this->input('no_hp') === $customer->no_hp ? [] : ['regex:/\A\+?[0-9][0-9\s()\-]{6,29}\z/'])],
            'no_kantor' => ['nullable', 'string', 'max:40', ...($editing && $this->input('no_kantor') === $customer->no_kantor ? [] : ['regex:/\A(?:[+0-9][0-9\s()\-]{5,29}|-)\z/'])],
            'nik' => ['nullable', 'string', 'max:255', ...($editing && $this->input('nik') === $customer->nik ? [] : ['regex:/\A(?:[0-9]{16}|-)\z/'])],
            'npwp' => ['required', 'string', 'max:255', ...($editing && $this->input('npwp') === $customer->npwp ? [] : ['regex:/\A(?:[0-9.\-]{15,24}|-)\z/'])],
            'provinsi_id' => ['required', 'integer', Rule::exists('wilayahs', 'id')->where('id_level_wilayah', 1)],
            'kabupaten_kota_id' => ['required', 'integer', Rule::exists('wilayahs', 'id')->where('id_level_wilayah', 2)->where('id_induk_wilayah', $province?->id_wilayah ?? '__invalid__')],
            'kecamatan_id' => ['nullable', 'integer', Rule::exists('wilayahs', 'id')->where('id_level_wilayah', 3)->where('id_induk_wilayah', $city?->id_wilayah ?? '__invalid__')],
            'alamat' => ['required', 'string', 'max:5000'], 'pembayaran' => ['required', 'integer', Rule::in([1, 2])],
            'plafon' => $editing ? ['exclude'] : ['exclude_unless:pembayaran,2', 'required', 'integer', 'min:0', 'max:2147483647'],
            'tempo_hari' => ['exclude_unless:pembayaran,2', 'required', 'integer', 'min:1', 'max:365'],
            'karyawan_id' => ['required', 'integer', Rule::exists('karyawans', 'id')->where(function ($query) use ($customer, $editing): void {
                if (! $editing || (string) $this->input('karyawan_id') !== (string) $customer->karyawan_id) {
                    $query->where('status', 1)->whereIn('jabatan_id', fn ($roles) => $roles->select('id')->from('jabatans')->where('is_sales', 1));
                }
            })],
        ];
    }
}
