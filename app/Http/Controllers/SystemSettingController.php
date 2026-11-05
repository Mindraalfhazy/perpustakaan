<?php

namespace App\Http\Controllers;

use App\Support\LibrarySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'title' => 'Pengaturan Sistem',
            'breadcrumb' => ['Dashboard', 'Pengaturan Sistem'],
            'lateFeePerDay' => LibrarySetting::lateFeePerDay(),
            'bookReplacementPrice' => LibrarySetting::bookReplacementPrice(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'late_fee_per_day' => ['required', 'integer', 'min:0', 'max:1000000'],
            'book_replacement_price' => ['required', 'integer', 'min:0', 'max:10000000'],
        ], [
            'late_fee_per_day.required' => 'Denda keterlambatan per hari wajib diisi.',
            'late_fee_per_day.integer' => 'Denda keterlambatan per hari harus berupa angka bulat.',
            'book_replacement_price.required' => 'Denda kehilangan per buku wajib diisi.',
            'book_replacement_price.integer' => 'Denda kehilangan per buku harus berupa angka bulat.',
        ]);

        LibrarySetting::setMany([
            'late_fee_per_day' => $validated['late_fee_per_day'],
            'book_replacement_price' => $validated['book_replacement_price'],
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Pengaturan denda berhasil diperbarui.');
    }
}
