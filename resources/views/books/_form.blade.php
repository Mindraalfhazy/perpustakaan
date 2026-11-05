<div class="row g-3">
    @php
        $coverUrl = $book->cover_path ? asset('storage/' . $book->cover_path) : null;
    @endphp

    <div class="col-md-12">
        <label class="form-label">Judul Buku *</label>
        <input type="text"
               name="judul"
               class="form-control"
               value="{{ old('judul', $book->judul) }}"
               required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Penulis *</label>
        <input type="text"
               name="penulis"
               class="form-control"
               value="{{ old('penulis', $book->penulis) }}"
               required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Tempat Terbit</label>
        <input type="text"
               name="tempat_terbit"
               class="form-control"
               value="{{ old('tempat_terbit', $book->tempat_terbit) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Penerbit</label>
        <input type="text"
               name="penerbit"
               class="form-control"
               value="{{ old('penerbit', $book->penerbit) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Tahun Terbit *</label>
        <input type="number"
               name="tahun"
               class="form-control"
               min="1900"
               max="2026"
               value="{{ old('tahun', $book->tahun) }}"
               required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Jumlah Stok *</label>
        <input type="number"
               name="stok"
               class="form-control"
               min="1"
               value="{{ old('stok', $book->stok ?: 1) }}"
               required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Kategori *</label>
        <select name="kategori" class="form-select" required>
            <option value="">Pilih kategori</option>

            @foreach ($categories as $category)
                <option value="{{ $category }}"
                    @selected(old('kategori', $book->kategori) === $category)>
                    {{ $category }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Upload Sampul Buku</label>
        <input type="file"
               name="cover"
               class="form-control"
               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <div class="form-text">
            Opsional. Cocok untuk buku fisik. Format: JPG, PNG, atau WEBP. Maksimal 2 MB.
        </div>
    </div>

    @if ($coverUrl)
        <div class="col-md-12">
            <div class="alert alert-light border d-flex align-items-center gap-3 mb-0">
                <img src="{{ $coverUrl }}"
                     alt="Sampul {{ $book->judul }}"
                     style="width:72px;height:96px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0;">
                <div>
                    <div class="fw-semibold">Sampul saat ini</div>
                    <div class="text-muted small">Upload file baru jika ingin mengganti sampul buku.</div>
                </div>
            </div>
        </div>
    @endif

    @unless($book->exists)
        <div class="col-md-12">
            <label class="form-label">Upload E-Book (PDF)</label>
            <input type="file"
                   name="ebook"
                   class="form-control"
                   accept="application/pdf">
            <div class="form-text">
                File akan disimpan di `storage/app/public/ebook/` dan hanya diakses melalui controller.
            </div>
        </div>
    @endunless

</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit"
            class="btn app-btn-primary">
        Simpan
    </button>

    <a href="{{ route('admin.books.index') }}"
       class="btn btn-outline-secondary">
        Batal
    </a>
</div>
