<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $member = $this->route('member');
        $memberId = is_object($member) ? $member->id : $member;
        $userId = is_object($member) ? $member->user_id : null;

        return [
            'nama' => 'required|string|max:255',
            'nisn' => ['required', 'digits:10', Rule::unique('members', 'nisn')->ignore($memberId)],
            'kelas' => 'required|string',
            'email' => [
                Rule::requiredIf(fn () => filled($this->input('password')) || filled($userId)),
                'nullable',
                'email',
                Rule::unique('members', 'email')->ignore($memberId),
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    if (blank($value)) {
                        return;
                    }

                    $existingUser = User::query()
                        ->where('email', $value)
                        ->when($userId, fn ($query) => $query->whereKeyNot($userId))
                        ->first();

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