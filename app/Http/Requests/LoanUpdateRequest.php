<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali' => 'required|date|after:tgl_pinjam',
        ];
    }
}
