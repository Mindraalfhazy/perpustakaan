@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .profile-card { border: 0; border-radius: 20px; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06); }
        .profile-avatar-preview {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(109, 40, 217, 0.12);
        }
        .profile-avatar-fallback {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #6D28D9;
            color: #fff;
            font-size: 32px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card profile-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Edit Profil</div>
                    <div class="text-muted small">Perbarui data pribadi, foto profil, dan informasi kontak.</div>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-12 d-flex flex-column flex-md-row align-items-md-center gap-3 mb-2">
                            <div>
                                @if ($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="profile-avatar-preview" id="profile-photo-preview">
                                @else
                                    <div class="profile-avatar-fallback" id="profile-photo-fallback">{{ $user->initials }}</div>
                                    <img src="" alt="{{ $user->name }}" class="profile-avatar-preview d-none" id="profile-photo-preview">
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label for="photo" class="form-label">Foto profil</label>
                                <input type="file" id="photo" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                <div class="form-text">Format `jpg`, `jpeg`, `png`, `webp`, maksimal 2MB. File disimpan di `storage/app/public/photos`.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($user->birth_date)->toDateString()) }}">
                        </div>

                        @if ($user->isAdmin())
                            <div class="col-md-6">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" id="nik" name="nik" class="form-control" inputmode="numeric" maxlength="16" value="{{ old('nik', $user->nik) }}" required>
                                <div class="form-text">NIK wajib 16 digit angka dan unik untuk akun administrator/guru.</div>
                            </div>
                        @endif

                        <div class="col-12">
                            <label for="bio" class="form-label d-flex justify-content-between">
                                <span>Bio</span>
                                <span class="text-muted small"><span id="bio-counter">{{ strlen(old('bio', $user->bio ?? '')) }}</span>/300</span>
                            </label>
                            <textarea id="bio" name="bio" rows="4" class="form-control" maxlength="300" placeholder="Tulis deskripsi singkat tentang diri Anda">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea id="address" name="address" rows="3" class="form-control" placeholder="Alamat lengkap">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn app-btn-primary px-4">Simpan Profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card profile-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Ringkasan Profil</div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3"><span class="text-muted small d-block">Nama</span><span class="fw-semibold">{{ $user->name }}</span></div>
                    <div class="mb-3"><span class="text-muted small d-block">Email</span><span class="fw-semibold">{{ $user->email }}</span></div>
                    <div class="mb-3"><span class="text-muted small d-block">Peran</span><span class="badge {{ $user->isAdmin() ? 'badge-soft-purple' : 'badge-soft-green' }}">{{ $user->isAdmin() ? 'Administrator / Guru' : 'Siswa' }}</span></div>
                    <div class="mb-3"><span class="text-muted small d-block">Kelas</span><span class="fw-semibold">{{ $user->displayClass() }}</span></div>
                    @if ($user->isAdmin())
                        <div><span class="text-muted small d-block">NIK Tersimpan</span><span class="fw-semibold">{{ $user->masked_nik }}</span></div>
                    @endif
                </div>
            </div>

            <div class="card profile-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Ubah Password</div>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('profile.password') }}" method="POST" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-secondary w-100">Perbarui Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const bio = document.getElementById('bio');
            const bioCounter = document.getElementById('bio-counter');
            const photoInput = document.getElementById('photo');
            const preview = document.getElementById('profile-photo-preview');
            const fallback = document.getElementById('profile-photo-fallback');

            bio?.addEventListener('input', () => {
                bioCounter.textContent = bio.value.length;
            });

            photoInput?.addEventListener('change', (event) => {
                const [file] = event.target.files;

                if (!file) {
                    return;
                }

                const url = URL.createObjectURL(file);
                preview.src = url;
                preview.classList.remove('d-none');
                fallback?.classList.add('d-none');
            });
        })();
    </script>
@endpush
