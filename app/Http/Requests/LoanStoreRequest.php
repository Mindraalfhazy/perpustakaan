<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('jumlah')) {
            $this->merge([
                'jumlah' => 1,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id'    => 'required|exists:members,id',
            'book_id'      => 'required|exists:books,id',
            'jumlah'       => 'required|integer|min:1',
            'tgl_pinjam'   => 'required|date',
            'tgl_kembali'  => 'required|date|after:tgl_pinjam',
        ];
    }
}
