@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .reader-shell { min-height: calc(100vh - 160px); }
        .reader-app { display: grid; grid-template-columns: 300px 1fr; border-radius: 24px; overflow: hidden; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12); }
        .reader-app.is-dark { background: #0f172a; color: #f8fafc; }
        .reader-app.toolbar-hidden .reader-toolbar { transform: translateY(-100%); opacity: 0; pointer-events: none; }
        .reader-app.toolbar-hidden .reader-progress { opacity: 0; }
        .reader-sidebar { background: #f8fafc; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; min-width: 0; }
        .reader-app.is-dark .reader-sidebar { background: #111827; border-right-color: #1f2937; }
        .reader-main { position: relative; min-width: 0; display: flex; flex-direction: column; background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 100%); }
        .reader-app.is-dark .reader-main { background: linear-gradient(180deg, #020617 0%, #0f172a 100%); }
        .reader-toolbar { position: sticky; top: 0; z-index: 20; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; padding: 14px 18px; background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(14px); border-bottom: 1px solid rgba(229, 231, 235, 0.9); transition: transform 0.2s ease, opacity 0.2s ease; }
        .reader-app.is-dark .reader-toolbar { background: rgba(15, 23, 42, 0.88); border-bottom-color: rgba(31, 41, 55, 0.95); }
        .reader-toolbar-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn-reader { min-height: 44px; border-radius: 14px; border: 1px solid #d1d5db; background: #fff; color: #111827; padding-inline: 14px; }
        .btn-reader:hover { background: #f9fafb; }
        .reader-app.is-dark .btn-reader { background: #111827; border-color: #334155; color: #f8fafc; }
        .reader-toolbar .form-control { width: 88px; min-height: 44px; border-radius: 14px; }
        .reader-status { color: #6b7280; font-size: 14px; }
        .reader-app.is-dark .reader-status { color: #cbd5e1; }
        .reader-canvas-wrap { position: relative; flex: 1; overflow: auto; display: flex; align-items: center; justify-content: center; padding: 24px; touch-action: none; }
        .reader-canvas-wrap.is-animating .reader-canvas { animation: pageFade 0.25s ease; }
        .reader-canvas { max-width: 100%; border-radius: 18px; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.2); background: #fff; }
        .reader-app.is-dark .reader-canvas { box-shadow: 0 18px 50px rgba(2, 6, 23, 0.5); }
        .reader-progress { position: sticky; bottom: 0; z-index: 15; height: 6px; background: rgba(229, 231, 235, 0.9); transition: opacity 0.2s ease; }
        .reader-progress-bar { height: 100%; width: 0; background: linear-gradient(90deg, #6D28D9 0%, #8B5CF6 100%); border-radius: 999px; transition: width 0.2s ease; }
        .loading-state { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.92); z-index: 25; }
        .reader-app.is-dark .loading-state { background: rgba(2, 6, 23, 0.92); }
        .sidebar-header { padding: 18px; border-bottom: 1px solid #e5e7eb; }
        .reader-app.is-dark .sidebar-header { border-bottom-color: #1f2937; }
        .sidebar-tabs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .reader-app.is-dark .sidebar-tabs { border-bottom-color: #1f2937; }
        .sidebar-tab { border: 0; border-radius: 12px; padding: 10px 12px; background: #e2e8f0; color: #334155; font-size: 14px; min-height: 42px; }
        .sidebar-tab.active { background: #6D28D9; color: #fff; }
        .reader-app.is-dark .sidebar-tab { background: #1e293b; color: #cbd5e1; }
        .sidebar-panel { display: none; padding: 14px 12px; overflow: auto; }
        .sidebar-panel.active { display: block; }
        .thumbnail-item,
        .bookmark-item,
        .outline-item { width: 100%; border: 0; background: transparent; text-align: left; padding: 10px; border-radius: 14px; margin-bottom: 8px; transition: background 0.2s ease; }
        .thumbnail-item:hover,
        .bookmark-item:hover,
        .outline-item:hover { background: rgba(109, 40, 217, 0.08); }
        .thumbnail-item.active,
        .bookmark-item.active { background: rgba(109, 40, 217, 0.16); }
        .thumbnail-canvas { width: 100%; height: auto; border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; }
        .reader-app.is-dark .thumbnail-canvas { border-color: #334155; }
        .panel-empty { padding: 12px 4px; color: #6b7280; font-size: 14px; }
        .reader-app.is-dark .panel-empty { color: #94a3b8; }
        .reader-meta-chip { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; border-radius: 999px; padding: 6px 12px; background: rgba(109, 40, 217, 0.12); color: #6D28D9; font-size: 13px; font-weight: 600; }
        .reader-app.is-dark .reader-meta-chip { background: rgba(139, 92, 246, 0.16); color: #c4b5fd; }
        .reader-stage-hint { position: absolute; right: 18px; bottom: 22px; z-index: 16; padding: 10px 14px; border-radius: 999px; background: rgba(17, 24, 39, 0.72); color: #fff; font-size: 12px; pointer-events: none; opacity: 0; transform: translateY(10px); transition: all 0.25s ease; }
        .reader-stage-hint.visible { opacity: 1; transform: translateY(0); }
        .mobile-sidebar-toggle { display: none; }
        @keyframes pageFade {
            from { opacity: 0.45; transform: translateX(12px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @media (max-width: 991.98px) {
            .reader-app { grid-template-columns: 1fr; }
            .reader-sidebar { display: none; border-right: 0; border-bottom: 1px solid #e5e7eb; max-height: 320px; }
            .reader-app.is-mobile-panel-open .reader-sidebar { display: flex; }
            .mobile-sidebar-toggle { display: inline-flex; }
            .reader-shell { min-height: calc(100vh - 140px); }
        }
        @media (max-width: 767.98px) {
            .reader-toolbar { padding: 12px; }
            .reader-toolbar-group { width: 100%; }
            .reader-toolbar-group .btn-reader,
            .reader-toolbar-group .form-control { flex: 1 1 auto; }
            .reader-canvas-wrap { padding: 16px 12px 20px; }
            .reader-canvas { border-radius: 14px; }
            .reader-stage-hint { left: 12px; right: 12px; bottom: 16px; text-align: center; }
        }
    </style>
@endpush

@section('content')
    <div class="reader-shell">
        <div class="reader-app" id="reader-app">
            <aside class="reader-sidebar">
                <div class="sidebar-header">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="reader-meta-chip">{{ $book->kategori }}</span>
                        <span class="reader-meta-chip">Resume Hal. {{ $readerResumePage }}</span>
                    </div>
                    <div class="fw-bold fs-5">{{ $book->judul }}</div>
                    <div class="small text-muted mt-1">{{ $book->penulis }} • {{ $book->tahun }}</div>
                </div>
                <div class="sidebar-tabs">
                    <button type="button" class="sidebar-tab active" data-panel-target="thumbnails-panel">Halaman</button>
                    <button type="button" class="sidebar-tab" data-panel-target="outline-panel">Daftar Isi</button>
                    <button type="button" class="sidebar-tab" data-panel-target="bookmarks-panel">Bookmark</button>
                </div>
                <div class="sidebar-panel active" id="thumbnails-panel">
                    <div class="panel-empty" id="thumbnails-empty">Memuat thumbnail halaman...</div>
                    <div id="thumbnails-list"></div>
                </div>
                <div class="sidebar-panel" id="outline-panel">
                    <div class="panel-empty" id="outline-empty">Daftar isi akan tampil jika PDF memilikinya.</div>
                    <div id="outline-list"></div>
                </div>
                <div class="sidebar-panel" id="bookmarks-panel">
                    <div class="panel-empty" id="bookmarks-empty">Belum ada bookmark tersimpan.</div>
                    <div id="bookmarks-list"></div>
                </div>
            </aside>

            <section class="reader-main">
                <div class="reader-toolbar" id="reader-toolbar">
                    <div class="reader-toolbar-group">
                        <a href="{{ route('ebooks.index') }}" class="btn btn-reader">Kembali</a>
                        <button type="button" class="btn btn-reader mobile-sidebar-toggle" id="open-mobile-panel">Panel</button>
                        <button type="button" class="btn btn-reader" id="prev-page">Prev</button>
                        <button type="button" class="btn btn-reader" id="next-page">Next</button>
                        <input type="number" class="form-control" min="1" value="1" id="page-number">
                        <span class="reader-status">/ <span id="page-count">0</span></span>
                    </div>
                    <div class="reader-toolbar-group">
                        <button type="button" class="btn btn-reader" id="zoom-out">-</button>
                        <span class="reader-status" id="zoom-label">100%</span>
                        <button type="button" class="btn btn-reader" id="zoom-in">+</button>
                        <button type="button" class="btn btn-reader" id="toggle-bookmark">Bookmark</button>
                        <button type="button" class="btn btn-reader" id="toggle-theme">Mode Gelap</button>
                        <button type="button" class="btn btn-reader" id="toggle-fullscreen">Fullscreen</button>
                    </div>
                </div>

                <div class="reader-canvas-wrap" id="reader-stage">
                    <div class="loading-state" id="loading-state">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <div>Memuat ebook...</div>
                        </div>
                    </div>
                    <div class="reader-stage-hint visible" id="stage-hint">Swipe kiri/kanan, pinch untuk zoom, tap tengah untuk toolbar.</div>
                    <canvas id="pdf-canvas" class="reader-canvas"></canvas>
                </div>
                <div class="reader-progress">
                    <div class="reader-progress-bar" id="reader-progress-bar"></div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (() => {
            const pdfUrl = @json($pdfUrl);
            const progressUrl = @json($readerProgressUrl);
            const bookId = @json($book->id);
            const userId = @json($readerUserId);
            const resumePageFromServer = @json($readerResumePage);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const pdfjsLib = window['pdfjs-dist/build/pdf'];
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const state = {
                pdfDoc: null,
                pageNum: 1,
                scale: 1.15,
                bookmarks: [],
                darkMode: false,
                isRendering: false,
                pendingPage: null,
                lastSyncAt: 0,
                quickZoomScale: 2.2,
                toolbarHidden: false,
            };

            const resumeKey = `ebook-reader:resume:${userId}:${bookId}`;
            const bookmarkKey = `ebook-reader:bookmarks:${userId}:${bookId}`;
            const themeKey = `ebook-reader:theme:${userId}:${bookId}`;

            const app = document.getElementById('reader-app');
            const stage = document.getElementById('reader-stage');
            const toolbar = document.getElementById('reader-toolbar');
            const canvas = document.getElementById('pdf-canvas');
            const ctx = canvas.getContext('2d', { alpha: false });
            const pageNumberInput = document.getElementById('page-number');
            const pageCountLabel = document.getElementById('page-count');
            const zoomLabel = document.getElementById('zoom-label');
            const loadingState = document.getElementById('loading-state');
            const progressBar = document.getElementById('reader-progress-bar');
            const stageHint = document.getElementById('stage-hint');
            const thumbnailsList = document.getElementById('thumbnails-list');
            const thumbnailsEmpty = document.getElementById('thumbnails-empty');
            const outlineList = document.getElementById('outline-list');
            const outlineEmpty = document.getElementById('outline-empty');
            const bookmarksList = document.getElementById('bookmarks-list');
            const bookmarksEmpty = document.getElementById('bookmarks-empty');
            function loadPersistedState() {
                const resumeData = JSON.parse(localStorage.getItem(resumeKey) || '{}');
                const bookmarkData = JSON.parse(localStorage.getItem(bookmarkKey) || '[]');
                const theme = localStorage.getItem(themeKey);
                const localPage = Number.isInteger(resumeData.page) ? resumeData.page : 1;
                const localScale = typeof resumeData.scale === 'number' ? resumeData.scale : 1.15;

                state.pageNum = Math.max(1, resumePageFromServer || 1, localPage);
                state.scale = localScale;
                state.bookmarks = Array.isArray(bookmarkData) ? bookmarkData : [];
                state.darkMode = theme === 'dark';
            }

            function persistReaderState() {
                localStorage.setItem(resumeKey, JSON.stringify({
                    page: state.pageNum,
                    scale: state.scale,
                }));
            }

            function persistBookmarks() {
                localStorage.setItem(bookmarkKey, JSON.stringify(state.bookmarks));
            }

            function applyTheme() {
                app.classList.toggle('is-dark', state.darkMode);
                document.getElementById('toggle-theme').textContent = state.darkMode ? 'Mode Terang' : 'Mode Gelap';
                localStorage.setItem(themeKey, state.darkMode ? 'dark' : 'light');
            }

            function updateProgressBar() {
                if (!state.pdfDoc?.numPages) {
                    progressBar.style.width = '0%';
                    return;
                }

                const progress = (state.pageNum / state.pdfDoc.numPages) * 100;
                progressBar.style.width = `${progress}%`;
            }

            function toggleToolbar(forceValue = null) {
                state.toolbarHidden = forceValue ?? !state.toolbarHidden;
                app.classList.toggle('toolbar-hidden', state.toolbarHidden);
            }

            async function syncProgress(eventName = 'heartbeat') {
                if (!state.pdfDoc) {
                    return;
                }

                const now = Date.now();
                if (eventName === 'heartbeat' && now - state.lastSyncAt < 15000) {
                    return;
                }

                state.lastSyncAt = now;

                try {
                    await fetch(progressUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            page: state.pageNum,
                            scale: state.scale,
                            event: eventName,
                        }),
                    });
                } catch (error) {
                    console.warn('Gagal sinkron progres ebook.', error);
                }
            }

            function refreshActiveStates() {
                document.querySelectorAll('.thumbnail-item, .bookmark-item').forEach((element) => {
                    const page = Number(element.dataset.page || 0);
                    element.classList.toggle('active', page === state.pageNum);
                });

                const isBookmarked = state.bookmarks.includes(state.pageNum);
                document.getElementById('toggle-bookmark').textContent = isBookmarked ? 'Hapus Bookmark' : 'Bookmark';
            }

            async function renderPage(pageNum, eventName = 'page-change') {
                if (!state.pdfDoc) {
                    return;
                }

                if (state.isRendering) {
                    state.pendingPage = pageNum;
                    return;
                }

                state.isRendering = true;
                stage.classList.add('is-animating');

                try {
                    const page = await state.pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: state.scale });
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    await page.render({
                        canvasContext: ctx,
                        viewport,
                    }).promise;

                    state.pageNum = pageNum;
                    pageNumberInput.value = pageNum;
                    pageCountLabel.textContent = state.pdfDoc.numPages;
                    zoomLabel.textContent = `${Math.round(state.scale * 100)}%`;
                    updateProgressBar();
                    persistReaderState();
                    refreshActiveStates();
                    syncProgress(eventName);
                } finally {
                    state.isRendering = false;
                    window.setTimeout(() => stage.classList.remove('is-animating'), 220);

                    if (state.pendingPage && state.pendingPage !== state.pageNum) {
                        const nextPage = state.pendingPage;
                        state.pendingPage = null;
                        await renderPage(nextPage, 'page-change');
                    }
                }
            }

            async function jumpToPage(pageNum, eventName = 'page-change') {
                if (!state.pdfDoc) {
                    return;
                }

                const nextPage = Math.min(Math.max(pageNum, 1), state.pdfDoc.numPages);
                await renderPage(nextPage, eventName);
            }

            async function renderThumbnails() {
                thumbnailsList.innerHTML = '';

                for (let pageNum = 1; pageNum <= state.pdfDoc.numPages; pageNum++) {
                    const page = await state.pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: 0.24 });
                    const thumbCanvas = document.createElement('canvas');
                    const thumbCtx = thumbCanvas.getContext('2d', { alpha: false });
                    thumbCanvas.className = 'thumbnail-canvas';
                    thumbCanvas.width = viewport.width;
                    thumbCanvas.height = viewport.height;

                    await page.render({
                        canvasContext: thumbCtx,
                        viewport,
                    }).promise;

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'thumbnail-item';
                    button.dataset.page = pageNum;
                    button.innerHTML = `<div class="small fw-semibold mb-2">Halaman ${pageNum}</div>`;
                    button.appendChild(thumbCanvas);
                    button.addEventListener('click', () => jumpToPage(pageNum));
                    thumbnailsList.appendChild(button);
                }

                thumbnailsEmpty.style.display = state.pdfDoc.numPages ? 'none' : 'block';
                refreshActiveStates();
            }

            async function resolveDestination(dest) {
                let destination = dest;

                if (typeof destination === 'string') {
                    destination = await state.pdfDoc.getDestination(destination);
                }

                if (!Array.isArray(destination) || !destination[0]) {
                    return null;
                }

                const pageIndex = await state.pdfDoc.getPageIndex(destination[0]);
                return pageIndex + 1;
            }

            async function renderOutline() {
                const outline = await state.pdfDoc.getOutline();
                outlineList.innerHTML = '';

                if (!outline || outline.length === 0) {
                    outlineEmpty.style.display = 'block';
                    return;
                }

                outlineEmpty.style.display = 'none';

                for (const item of outline) {
                    const pageNum = await resolveDestination(item.dest);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'outline-item';
                    button.textContent = item.title || 'Bagian tanpa judul';
                    if (pageNum) {
                        button.addEventListener('click', () => jumpToPage(pageNum));
                    } else {
                        button.disabled = true;
                    }
                    outlineList.appendChild(button);
                }
            }

            function renderBookmarks() {
                bookmarksList.innerHTML = '';

                if (state.bookmarks.length === 0) {
                    bookmarksEmpty.style.display = 'block';
                    return;
                }

                bookmarksEmpty.style.display = 'none';

                [...state.bookmarks].sort((a, b) => a - b).forEach((pageNum) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'bookmark-item';
                    button.dataset.page = pageNum;
                    button.textContent = `Halaman ${pageNum}`;
                    button.addEventListener('click', () => jumpToPage(pageNum));
                    bookmarksList.appendChild(button);
                });

                refreshActiveStates();
            }

            function toggleBookmark() {
                if (state.bookmarks.includes(state.pageNum)) {
                    state.bookmarks = state.bookmarks.filter((page) => page !== state.pageNum);
                } else {
                    state.bookmarks.push(state.pageNum);
                }

                persistBookmarks();
                renderBookmarks();
                syncProgress('bookmark');
            }

            async function loadPdfDocument() {
                loadPersistedState();
                applyTheme();

                const response = await fetch(pdfUrl, {
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat file ebook.');
                }

                const fileData = await response.arrayBuffer();
                state.pdfDoc = await pdfjsLib.getDocument({ data: fileData }).promise;
                state.pageNum = Math.min(Math.max(state.pageNum, 1), state.pdfDoc.numPages);
                await renderPage(state.pageNum, 'resume');
                await renderThumbnails();
                await renderOutline();
                renderBookmarks();
            }

            document.getElementById('prev-page').addEventListener('click', () => jumpToPage(state.pageNum - 1));
            document.getElementById('next-page').addEventListener('click', () => jumpToPage(state.pageNum + 1));
            document.getElementById('zoom-in').addEventListener('click', () => {
                state.scale = Math.min(state.scale + 0.15, 3);
                jumpToPage(state.pageNum, 'zoom');
            });
            document.getElementById('zoom-out').addEventListener('click', () => {
                state.scale = Math.max(state.scale - 0.15, 0.7);
                jumpToPage(state.pageNum, 'zoom');
            });
            document.getElementById('toggle-bookmark').addEventListener('click', toggleBookmark);
            document.getElementById('toggle-theme').addEventListener('click', () => {
                state.darkMode = !state.darkMode;
                applyTheme();
            });
            document.getElementById('toggle-fullscreen').addEventListener('click', async () => {
                if (!document.fullscreenElement) {
                    await stage.requestFullscreen();
                    toggleToolbar(true);
                } else {
                    await document.exitFullscreen();
                    toggleToolbar(false);
                }
            });
            document.getElementById('open-mobile-panel').addEventListener('click', () => {
                app.classList.toggle('is-mobile-panel-open');
            });

            pageNumberInput.addEventListener('change', () => jumpToPage(Number(pageNumberInput.value)));

            document.querySelectorAll('.sidebar-tab').forEach((tab) => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.sidebar-tab').forEach((button) => button.classList.remove('active'));
                    document.querySelectorAll('.sidebar-panel').forEach((panel) => panel.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById(tab.dataset.panelTarget).classList.add('active');
                });
            });

            let touchStartX = 0;
            let touchStartY = 0;
            let initialPinchDistance = null;
            let initialScale = state.scale;
            let lastTapAt = 0;

            function getDistance(touches) {
                const dx = touches[0].clientX - touches[1].clientX;
                const dy = touches[0].clientY - touches[1].clientY;
                return Math.sqrt((dx * dx) + (dy * dy));
            }

            stage.addEventListener('touchstart', (event) => {
                if (event.touches.length === 1) {
                    touchStartX = event.touches[0].clientX;
                    touchStartY = event.touches[0].clientY;
                }

                if (event.touches.length === 2) {
                    initialPinchDistance = getDistance(event.touches);
                    initialScale = state.scale;
                }
            }, { passive: true });

            stage.addEventListener('touchmove', (event) => {
                if (event.touches.length === 2 && initialPinchDistance) {
                    event.preventDefault();
                    const nextDistance = getDistance(event.touches);
                    const zoomFactor = nextDistance / initialPinchDistance;
                    state.scale = Math.min(3, Math.max(0.7, initialScale * zoomFactor));
                    zoomLabel.textContent = `${Math.round(state.scale * 100)}%`;
                }
            }, { passive: false });

            stage.addEventListener('touchend', (event) => {
                if (initialPinchDistance && event.touches.length < 2) {
                    initialPinchDistance = null;
                    jumpToPage(state.pageNum, 'zoom');
                }

                if (event.changedTouches.length === 1) {
                    const touch = event.changedTouches[0];
                    const deltaX = touch.clientX - touchStartX;
                    const deltaY = touch.clientY - touchStartY;
                    const absoluteX = Math.abs(deltaX);
                    const absoluteY = Math.abs(deltaY);

                    if (absoluteX > 70 && absoluteY < 60 && state.scale <= 1.4) {
                        jumpToPage(deltaX < 0 ? state.pageNum + 1 : state.pageNum - 1, 'swipe');
                    } else if (absoluteX < 12 && absoluteY < 12) {
                        const now = Date.now();
                        const centerZone = touch.clientX > window.innerWidth * 0.3 && touch.clientX < window.innerWidth * 0.7;

                        if (now - lastTapAt < 260) {
                            state.scale = state.scale > 1.4 ? 1.15 : state.quickZoomScale;
                            jumpToPage(state.pageNum, 'double-tap');
                        } else if (centerZone && document.fullscreenElement) {
                            toggleToolbar();
                        }

                        lastTapAt = now;
                    }
                }
            });

            stage.addEventListener('click', (event) => {
                if (!document.fullscreenElement || event.target !== canvas) {
                    return;
                }

                const rect = canvas.getBoundingClientRect();
                const relativeX = event.clientX - rect.left;
                const centerZone = relativeX > rect.width * 0.3 && relativeX < rect.width * 0.7;

                if (centerZone) {
                    toggleToolbar();
                }
            });

            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement) {
                    toggleToolbar(false);
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    syncProgress('hidden');
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.ctrlKey && ['s', 'p', 'u'].includes(event.key.toLowerCase())) {
                    event.preventDefault();
                    return;
                }

                if (event.key === 'ArrowRight') {
                    jumpToPage(state.pageNum + 1);
                }

                if (event.key === 'ArrowLeft') {
                    jumpToPage(state.pageNum - 1);
                }
            });

            app.addEventListener('contextmenu', (event) => event.preventDefault());

            window.setInterval(() => {
                if (!document.hidden) {
                    syncProgress('heartbeat');
                }
            }, 20000);

            window.setTimeout(() => stageHint.classList.remove('visible'), 3200);

            loadPdfDocument()
                .then(() => {
                    loadingState.style.display = 'none';
                })
                .catch((error) => {
                    loadingState.innerHTML = `<div class="text-center text-danger px-3">${error.message}</div>`;
                });
        })();
    </script>
@endpush
