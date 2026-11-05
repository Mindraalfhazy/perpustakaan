<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Lengkap*</label>
        <input type="text" name="nama" class="form-control" value="{{ old('nama', $member->nama) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">NISN*</label>
        <input type="text" name="nisn" class="form-control" maxlength="10" value="{{ old('nisn', $member->nisn) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Kelas*</label>
        <select name="kelas" class="form-select" required>
            <option value="">Pilih kelas</option>
            @foreach ($kelasOptions as $kelas)
                <option value="{{ $kelas }}" @selected(old('kelas', $member->kelas) === $kelas)>{{ $kelas }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
        <div class="form-text">Wajib diisi jika ingin membuat atau mengganti password login anggota.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ $member->exists ? 'Password Baru' : 'Password Login' }}</label>
        <input type="password" name="password" class="form-control">
        <div class="form-text">{{ $member->exists ? 'Kosongkan jika password tidak ingin diubah.' : 'Isi untuk langsung membuat akun login anggota.' }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn app-btn-primary">Simpan</button>
    <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">Batal</a>
</div>
