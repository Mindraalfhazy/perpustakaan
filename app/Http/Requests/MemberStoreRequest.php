<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nisn' => 'required|digits:10|unique:members,nisn',
            'kelas' => 'required|string',
            'email' => [
                Rule::requiredIf(fn () => filled($this->input('password'))),
                'nullable',
                'email',
                'unique:members,email',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $existingUser = User::query()->where('email', $value)->first();

                    if (!$existingUser) {
                        return;
                    }

                    if ($existingUser->isSiswa() && $existingUser->nisn === $this->input('nisn')) {
                        return;
                    }

                    $fail('Email sudah digunakan oleh akun lain.');
                },
            ],
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }
}