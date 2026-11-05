<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul'            => 'required|string|max:255',
            'penulis'          => 'required|string|max:255',
            'tempat_terbit'    => 'nullable|string|max:255',
            'penerbit'         => 'nullable|string|max:255',
            'tahun'            => 'required|integer|min:1900|max:2026',
            'kategori'         => 'required|in:Pelajaran,Fiksi,Non-Fiksi,Referensi,Lainnya',
            'stok'             => 'required|integer|min:1',
            'cover'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'judul'            => 'judul buku',
            'penulis'          => 'penulis',
            'tempat_terbit'    => 'tempat terbit',
            'penerbit'         => 'penerbit',
            'tahun'            => 'tahun terbit',
            'kategori'         => 'kategori',
            'stok'             => 'jumlah stok',
            'cover'            => 'sampul buku',
        ];
    }
}
