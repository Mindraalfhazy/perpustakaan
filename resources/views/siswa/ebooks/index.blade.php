@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .ebook-catalog-card {
            border: 0;
            overflow: hidden;
            border-radius: 22px;
            box-shadow: 0 20px 40px -28px rgba(15, 23, 42, 0.34);
            background: linear-gradient(145deg, rgba(255,255,255,0.98), rgba(248,250,252,0.95));
        }
        .ebook-cover-preview {
            position: relative;
            aspect-ratio: 3 / 4;
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(160deg, #312E81 0%, #4F46E5 55%, #06B6D4 100%);
            box-shadow: 0 22px 36px -24px rgba(49, 46, 129, 0.7);
        }
        .ebook-cover-preview canvas {
            width: 100%;
            height: 100%;
            display: block;
            background: #fff;
        }
        .ebook-cover-preview img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            background: #fff;
        }
        .ebook-cover-fallback {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 18px;
            color: #fff;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.08), rgba(15, 23, 42, 0.72)),
                linear-gradient(135deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04));
            transition: opacity 0.2s ease;
        }
        .ebook-cover-preview.is-rendered .ebook-cover-fallback { opacity: 0; pointer-events: none; }
        .ebook-cover-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: auto;
            background: rgba(255,255,255,0.16);
            backdrop-filter: blur(6px);
        }
        .ebook-cover-icon i { width: 24px; height: 24px; }
        .ebook-cover-title {
            font-weight: 700;
            line-height: 1.35;
            font-size: 0.96rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .ebook-cover-meta { font-size: 0.78rem; opacity: 0.9; margin-top: 4px; }
        .ebook-cover-loading {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
        }
        .ebook-cover-preview.is-rendered .ebook-cover-loading { display: none; }
        .ebook-cover-loading .spinner-border { width: 1rem; height: 1rem; }
    </style>
@endpush

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-md-5">
                    <label for="search" class="form-label">Cari E-Book</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Judul atau penulis..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select id="kategori" name="kategori" class="form-select">
                        @foreach ($kategoriOptions as $option)
                            <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-outline-secondary">Terapkan Filter</button>
                </div>
            </form>

            <div class="row g-4">
                @forelse ($books as $book)
                    <div class="col-md-6 col-xl-4">
                        <div class="card ebook-catalog-card h-100">
                            <div class="card-body d-flex flex-column">
                                @if ($book->cover_path)
                                    <div class="ebook-cover-preview mb-4 is-rendered">
                                        <img src="{{ asset('storage/' . $book->cover_path) }}" alt="Sampul {{ $book->judul }}">
                                    </div>
                                @else
                                    <div class="ebook-cover-preview mb-4"
                                         data-ebook-preview
                                         data-pdf-url="{{ route('ebooks.file', $book) }}"
                                         data-title="{{ $book->judul }}"
                                         data-author="{{ $book->penulis }}">
                                        <canvas></canvas>
                                        <div class="ebook-cover-loading">
                                            <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                                        </div>
                                        <div class="ebook-cover-fallback">
                                            <span class="ebook-cover-icon"><i data-feather="book-open"></i></span>
                                            <div class="ebook-cover-title">{{ $book->judul }}</div>
                                            <div class="ebook-cover-meta">{{ $book->penulis }}</div>
                                        </div>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <span class="badge badge-soft-purple">{{ $book->kategori }}</span>
                                    <span class="badge badge-soft-green">Ada Ebook</span>
                                </div>
                                <h3 class="h6 fw-bold">{{ $book->judul }}</h3>
                                <div class="text-muted mb-1">Penulis: {{ $book->penulis }}</div>
                                <div class="text-muted mb-3">Tahun: {{ $book->tahun }}</div>
                                <div class="small text-muted mb-3">Stok fisik: {{ $book->stok }} buku</div>
                                <div class="mt-auto">
                                    <a href="{{ route('ebooks.read', $book) }}" class="btn app-btn-primary w-100 d-flex justify-content-center align-items-center gap-2">
                                        <i data-feather="book-open" style="width: 18px; height: 18px;"></i> Baca Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-5">Belum ada ebook yang tersedia.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $books->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (() => {
            const previews = document.querySelectorAll('[data-ebook-preview]');

            if (!previews.length) {
                return;
            }

            const pdfjsLib = window['pdfjs-dist/build/pdf'];
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const renderPreview = async (element) => {
                if (element.dataset.rendered === 'true') {
                    return;
                }

                element.dataset.rendered = 'true';

                try {
                    const pdf = await pdfjsLib.getDocument({
                        url: element.dataset.pdfUrl,
                        withCredentials: true,
                    }).promise;

                    const page = await pdf.getPage(1);
                    const viewport = page.getViewport({ scale: 0.62 });
                    const canvas = element.querySelector('canvas');
                    const context = canvas.getContext('2d', { alpha: false });

                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    await page.render({
                        canvasContext: context,
                        viewport,
                    }).promise;

                    element.classList.add('is-rendered');
                } catch (error) {
                    console.warn('Gagal memuat preview sampul e-book.', error);
                    element.classList.remove('is-rendered');
                }
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        renderPreview(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '120px 0px' });

            previews.forEach((element) => observer.observe(element));
        })();
    </script>
@endpush
