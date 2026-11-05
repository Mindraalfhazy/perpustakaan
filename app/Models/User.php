<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    public const ROLE_ADMINISTRATOR = 'administrator';
    public const ROLE_SISWA = 'siswa';

    public const ROLE_ADMIN = self::ROLE_ADMINISTRATOR;
    public const ROLE_PUSTAKAWAN = self::ROLE_ADMINISTRATOR;
    public const ROLE_ANGGOTA = self::ROLE_SISWA;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nisn',
        'kelas',
        'photo',
        'bio',
        'phone',
        'nik',
        'birth_date',
        'address',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'password' => 'hashed',
    ];

    protected function jabatan(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!empty($this->role)) {
                    return ucfirst($this->role);
                }

                $role = $this->getRoleNames()->first();

                return $role ?: '-';
            }
        );
    }

    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo ? Storage::disk('public')->url($this->photo) : null,
        );
    }

    protected function initials(): Attribute
    {
        return Attribute::make(
            get: function () {
                return collect(explode(' ', (string) $this->name))
                    ->filter()
                    ->take(2)
                    ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
                    ->implode('') ?: 'U';
            }
        );
    }

    protected function maskedNik(): Attribute
    {
        return Attribute::make(
            get: fn () => filled($this->nik) && strlen((string) $this->nik) > 4
                ? str_repeat('x', max(strlen((string) $this->nik) - 4, 0)) . substr((string) $this->nik, -4)
                : ($this->nik ?: '-')
        );
    }

    public function card(): HasOne
    {
        return $this->hasOne(Card::class);
    }

    public function borrows(): HasMany
    {
        return $this->hasMany(Borrow::class);
    }

    public function ebookReadingLogs(): HasMany
    {
        return $this->hasMany(EbookReadingLog::class);
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function resolveRole(): string
    {
        $role = strtolower((string) $this->getAttribute('role'));

        if (in_array($role, [self::ROLE_ADMINISTRATOR, self::ROLE_SISWA], true)) {
            return $role;
        }

        return match (strtolower((string) $this->getRoleNames()->first())) {
            'admin',
            'administrator',
            'pustakawan' => self::ROLE_ADMINISTRATOR,
            'anggota',
            'siswa' => self::ROLE_SISWA,
            default => self::ROLE_SISWA,
        };
    }

    public function isAdmin(): bool
    {
        return $this->resolveRole() === self::ROLE_ADMINISTRATOR;
    }

    public function isSiswa(): bool
    {
        return $this->resolveRole() === self::ROLE_SISWA;
    }

    public function canManageMembersMenu(): bool
    {
        return $this->isAdmin();
    }

    public function displayClass(): string
    {
        return $this->kelas
            ?? $this->member?->kelas
            ?? 'Belum diatur';
    }

    public function homeRoute(): string
    {
        return $this->isAdmin() ? 'dashboard' : 'siswa.dashboard';
    }
}
