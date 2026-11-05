@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .book-catalog-card {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 42px -30px rgba(15, 23, 42, 0.32);
            background: linear-gradient(145deg, rgba(255,255,255,0.98), rgba(248,250,252,0.95));
        }
        .book-media-frame {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #0F172A 0%, #1D4ED8 48%, #06B6D4 100%);
        }
        .book-media-frame canvas {
            width: 100%;
            height: 100%;
            display: block;
            background: #fff;
        }
        .book-media-frame img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            background: #fff;
        }
        .book-media-frame .fallback {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 16px;
            color: #fff;
            background: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.78));
        }
        .book-media-frame .fallback-badge {
            margin-bottom: auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            width: fit-content;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .book-media-frame .fallback-title {
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .book-media-frame .fallback-meta { font-size: 0.78rem; opacity: 0.92; margin-top: 4px; }
        .book-media-frame .loading-chip {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(6px);
        }
        .book-media-frame:not(.is-rendered) canvas { display: none; }
        .book-media-frame:not(.has-preview) .loading-chip { display: none; }
        .book-media-frame.is-rendered .fallback,
        .book-media-frame.is-rendered .loading-chip { display: none; }
    </style>
@endpush

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-md-5">
                    <label for="search" class="form-label">Cari Buku</label>
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
                        <div class="card book-catalog-card h-100">
                            <div class="card-body">
                                @if ($book->cover_path)
                                    <div class="book-media-frame is-rendered">
                                        <img src="{{ asset('storage/' . $book->cover_path) }}" alt="Sampul {{ $book->judul }}">
                                    </div>
                                @else
                                    <div class="book-media-frame {{ $book->has_ebook ? 'has-preview' : '' }}"
                                         @if ($book->has_ebook)
                                            data-ebook-preview
                                            data-pdf-url="{{ route('ebooks.file', $book) }}"
                                         @endif>
                                        <canvas></canvas>
                                        <div class="loading-chip">
                                            <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                                        </div>
                                        <div class="fallback">
                                            <span class="fallback-badge">
                                                <i data-feather="{{ $book->has_ebook ? 'monitor' : 'book' }}" style="width: 14px; height: 14px;"></i>
                                                {{ $book->has_ebook ? 'Preview E-Book' : 'Buku Fisik' }}
                                            </span>
                                            <div class="fallback-title">{{ $book->judul }}</div>
                                            <div class="fallback-meta">{{ $book->penulis }}</div>
                                        </div>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <span class="badge badge-soft-purple">{{ $book->kategori }}</span>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <span class="badge {{ $book->tersedia > 0 ? 'badge-soft-green' : 'badge-soft-red' }}">
                                            {{ $book->tersedia > 0 ? $book->tersedia . ' tersedia' : 'Stok habis' }}
                                        </span>
                                        @if ($book->has_ebook)
                                            <span class="badge badge-soft-purple">Ada Ebook</span>
                                        @endif
                                    </div>
                                </div>
                                <h3 class="h6 fw-bold">{{ $book->judul }}</h3>
                                <div class="text-muted mb-1">Penulis: {{ $book->penulis }}</div>
                                <div class="text-muted mb-3">Tahun: {{ $book->tahun }}</div>
                                <div class="small text-muted">Total stok: {{ $book->stok }} buku</div>
                                @if ($book->has_ebook)
                                    <div class="mt-3">
                                        <a href="{{ route('ebooks.read', $book) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="padding: 6px 12px; border-radius: 8px;">
                                            <i data-feather="book-open" style="width: 14px; height: 14px;"></i> Baca Ebook
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-5">Tidak ada buku yang sesuai filter.</div>
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
                    const viewport = page.getViewport({ scale: 0.55 });
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
                    console.warn('Gagal memuat preview e-book di katalog buku.', error);
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
