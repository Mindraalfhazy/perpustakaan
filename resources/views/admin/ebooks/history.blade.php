@extends('layouts.app')

@section('title', $title)

@push('styles')
    <style>
        .history-card,
        .history-chart-card,
        .history-table-card { border: 0; border-radius: 20px; box-shadow: 0 18px 36px -24px rgba(15, 23, 42, 0.28); }
        .stats-card {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            border: 0;
            background: linear-gradient(145deg, rgba(255,255,255,0.98), rgba(248,250,252,0.95));
            box-shadow: 0 24px 48px -30px rgba(15, 23, 42, 0.35);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stats-card::after {
            content: "";
            position: absolute;
            top: -36px;
            right: -36px;
            width: 118px;
            height: 118px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--stats-glow, rgba(79, 70, 229, 0.22)), transparent 70%);
        }
        .stats-card:hover { transform: translateY(-4px); box-shadow: 0 28px 54px -30px rgba(15, 23, 42, 0.4); }
        .stats-card .card-body { position: relative; z-index: 1; }
        .stats-icon { width: 52px; height: 52px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; }
        .stats-card.stats-indigo { --stats-glow: rgba(79, 70, 229, 0.28); }
        .stats-card.stats-emerald { --stats-glow: rgba(5, 150, 105, 0.26); }
        .stats-card.stats-amber { --stats-glow: rgba(217, 119, 6, 0.24); }
        .stats-card.stats-blue { --stats-glow: rgba(37, 99, 235, 0.24); }
        .history-filter-actions .btn,
        .history-mobile-card .btn,
        .history-toolbar .btn { min-height: 44px; }
        .history-mobile-card { border: 1px solid #ede9fe; border-radius: 16px; padding: 16px; background: #fff; box-shadow: 0 6px 20px rgba(109, 40, 217, 0.06); }
        .history-mobile-card + .history-mobile-card { margin-top: 12px; }
        .chart-canvas-wrap { position: relative; min-height: 300px; }
        .history-highlight-item + .history-highlight-item { border-top: 1px solid #f3f4f6; }
        .preset-pill { border-radius: 999px; }
        @media (max-width: 767.98px) {
            .chart-canvas-wrap { min-height: 240px; }
        }
    </style>
@endpush

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-indigo h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Sesi Baca</div>
                        <div class="fs-3 fw-bold">{{ $summary['total_sessions'] }}</div>
                    </div>
                    <div class="stats-icon" style="background:#EEF2FF;color:#4F46E5;"><i data-feather="activity"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-emerald h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Pembaca Aktif</div>
                        <div class="fs-3 fw-bold">{{ $summary['unique_readers'] }}</div>
                    </div>
                    <div class="stats-icon" style="background:#ECFDF5;color:#059669;"><i data-feather="users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-amber h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Hari Teraktif</div>
                        <div class="fw-bold">{{ $summary['most_active_day'] }}</div>
                        <div class="small text-muted">{{ $summary['most_active_day_total'] }} sesi</div>
                    </div>
                    <div class="stats-icon" style="background:#FFFBEB;color:#D97706;"><i data-feather="calendar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card stats-blue h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">E-Book Teratas</div>
                        <div class="fw-bold">{{ $summary['top_book'] }}</div>
                        <div class="small text-muted">Rata-rata {{ $summary['average_duration'] }}/sesi</div>
                    </div>
                    <div class="stats-icon" style="background:#EFF6FF;color:#2563EB;"><i data-feather="book-open"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card history-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">History Pembaca E-Book</h2>
                    <div class="text-muted">Pantau sesi baca siswa, filter per kelas/kategori, lalu cetak atau ekspor laporan periodik.</div>
                </div>
                <div class="d-flex flex-wrap gap-2 history-toolbar">
                    <a href="{{ route('admin.ebooks.history.print', array_merge(request()->query(), ['report_period' => request('report_period', 'monthly')])) }}" target="_blank" class="btn btn-outline-secondary">Print Preview</a>
                    <a href="{{ route('admin.ebooks.history.export-pdf', array_merge(request()->query(), ['report_period' => request('report_period', 'monthly')])) }}" class="btn btn-outline-danger">Export PDF</a>
                    <a href="{{ route('admin.ebooks.history.export-csv', array_merge(request()->query(), ['report_period' => request('report_period', 'monthly')])) }}" class="btn app-btn-primary">Export CSV</a>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach ([
                    'today' => 'Hari Ini',
                    'this_week' => 'Minggu Ini',
                    'this_month' => 'Bulan Ini',
                    'this_year' => 'Tahun Ini',
                    'custom' => 'Kustom',
                ] as $rangeValue => $rangeLabelOption)
                    <a href="{{ route('admin.ebooks.history', array_merge(request()->query(), ['range' => $rangeValue])) }}"
                        class="btn btn-sm {{ $filters['range'] === $rangeValue ? 'app-btn-primary' : 'btn-outline-secondary' }} preset-pill">
                        {{ $rangeLabelOption }}
                    </a>
                @endforeach
                <span class="badge badge-soft-purple align-self-center px-3 py-2">{{ $rangeLabel }}</span>
            </div>

            <form method="GET" id="history-filter-form" class="row g-3 history-filter-actions">
                <div class="col-lg-3 col-md-6">
                    <label for="student" class="form-label">Cari siswa</label>
                    <input type="text" id="student" name="student" value="{{ $filters['student'] }}" class="form-control js-debounce-submit" placeholder="Ketik nama siswa">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label for="book" class="form-label">Cari e-book</label>
                    <input type="text" id="book" name="book" value="{{ $filters['book'] }}" class="form-control js-debounce-submit" placeholder="Ketik judul buku">
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="kelas" class="form-label">Kelas</label>
                    <select id="kelas" name="kelas" class="form-select">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasOptions as $kelas)
                            <option value="{{ $kelas }}" @selected($filters['kelas'] === $kelas)>{{ $kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="category" class="form-label">Kategori</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach ($categoryOptions as $category)
                            <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="range" class="form-label">Preset Filter</label>
                    <select id="range" name="range" class="form-select">
                        <option value="today" @selected($filters['range'] === 'today')>Hari Ini</option>
                        <option value="this_week" @selected($filters['range'] === 'this_week')>Minggu Ini</option>
                        <option value="this_month" @selected($filters['range'] === 'this_month')>Bulan Ini</option>
                        <option value="this_year" @selected($filters['range'] === 'this_year')>Tahun Ini</option>
                        <option value="custom" @selected($filters['range'] === 'custom')>Kustom</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="date_from" class="form-label">Dari</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-4">
                    <label for="date_to" class="form-label">Sampai</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-lg-3 col-md-4">
                    <label for="report_period" class="form-label">Periode Report</label>
                    <select id="report_period" name="report_period" class="form-select">
                        @foreach ($reportPeriods as $periodValue => $periodLabel)
                            <option value="{{ $periodValue }}" @selected(request('report_period', 'monthly') === $periodValue)>{{ $periodLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn app-btn-primary">Terapkan Filter</button>
                    <a href="{{ route('admin.ebooks.history') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card history-chart-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Tren Aktivitas Baca</div>
                    <div class="text-muted small">Agregasi jumlah sesi per hari berdasarkan filter aktif.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-canvas-wrap"><canvas id="activity-chart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card history-chart-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">E-Book Terpopuler</div>
                    <div class="text-muted small">Top judul berdasarkan jumlah sesi tanpa memuat seluruh log ke browser.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-canvas-wrap"><canvas id="popular-books-chart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card history-table-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Riwayat Baca Siswa</div>
                    <div class="text-muted small">Daftar dibatasi pagination server-side agar tetap ringan untuk data besar.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Siswa</th>
                                        <th>Kelas</th>
                                        <th>E-Book</th>
                                        <th>Dibuka</th>
                                        <th>Terakhir Dibaca</th>
                                        <th>Durasi</th>
                                        <th>Perangkat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $log->user?->name ?? '-' }}</div>
                                                <span class="text-muted small">{{ $log->user?->nisn ?? 'Tanpa NISN' }}</span>
                                            </td>
                                            <td><span class="badge badge-soft-gray">{{ $log->user?->displayClass() ?? '-' }}</span></td>
                                            <td>
                                                <div class="fw-semibold">{{ $log->book?->judul ?? '-' }}</div>
                                                <span class="badge badge-soft-purple">{{ $log->book?->kategori ?? 'E-Book' }}</span>
                                            </td>
                                            <td>{{ $log->opened_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td>{{ $log->last_read_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td>{{ $log->duration_label }}</td>
                                            <td>
                                                <span class="badge {{ $log->device_type === 'mobile' ? 'badge-soft-green' : 'badge-soft-gray' }}">{{ ucfirst($log->device_type) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada history baca e-book pada rentang ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-md-none">
                        @forelse ($logs as $log)
                            <div class="history-mobile-card">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div>
                                        <div class="fw-semibold">{{ $log->user?->name ?? '-' }}</div>
                                        <div class="small text-muted">{{ $log->user?->displayClass() ?? '-' }}</div>
                                        <div class="text-muted small">{{ $log->book?->judul ?? '-' }}</div>
                                    </div>
                                    <span class="badge {{ $log->device_type === 'mobile' ? 'badge-soft-green' : 'badge-soft-gray' }}">{{ ucfirst($log->device_type) }}</span>
                                </div>
                                <div class="small text-muted mb-1">Dibuka: {{ $log->opened_at?->format('d M Y H:i') ?? '-' }}</div>
                                <div class="small text-muted mb-1">Terakhir baca: {{ $log->last_read_at?->format('d M Y H:i') ?? '-' }}</div>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <span class="badge badge-soft-purple">{{ $log->book?->kategori ?? 'E-Book' }}</span>
                                    <span class="badge badge-soft-yellow">{{ $log->duration_label }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Belum ada history baca e-book pada rentang ini.</div>
                        @endforelse
                    </div>

                    <div class="mt-4">{{ $logs->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card history-chart-card mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Siswa Paling Aktif</div>
                    <div class="text-muted small">Top pembaca berdasarkan jumlah sesi setelah filter diterapkan.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="chart-canvas-wrap"><canvas id="active-readers-chart"></canvas></div>
                </div>
            </div>

            <div class="card history-chart-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="fw-semibold">Ringkasan Cepat</div>
                    <div class="text-muted small">Sorotan buku dan siswa paling menonjol untuk periode aktif.</div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Top Buku</div>
                        @forelse ($highlights['popularBooks'] as $item)
                            <div class="history-highlight-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->book?->judul ?? '-' }}</div>
                                    <div class="small text-muted">{{ $item->display_duration }}</div>
                                </div>
                                <span class="badge badge-soft-purple">{{ $item->total_sessions }} sesi</span>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada data buku.</div>
                        @endforelse
                    </div>

                    <div>
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Top Siswa</div>
                        @forelse ($highlights['activeReaders'] as $item)
                            <div class="history-highlight-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->user?->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ $item->user?->displayClass() ?? '-' }} · {{ $item->display_duration }}</div>
                                </div>
                                <span class="badge badge-soft-green">{{ $item->total_sessions }} sesi</span>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada data siswa.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const chartData = @json($chartData);
            const form = document.getElementById('history-filter-form');
            let debounceTimer;

            document.querySelectorAll('.js-debounce-submit').forEach((input) => {
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => form.submit(), 450);
                });
            });

            ['kelas', 'category', 'range', 'report_period'].forEach((id) => {
                document.getElementById(id)?.addEventListener('change', () => form.submit());
            });

            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#9CA3AF', maxRotation: 0 }, grid: { color: 'rgba(156,163,175,0.12)' } },
                    y: { ticks: { color: '#9CA3AF', precision: 0 }, grid: { color: 'rgba(156,163,175,0.12)' } },
                },
            };

            new Chart(document.getElementById('activity-chart'), {
                type: 'line',
                data: {
                    labels: chartData.activityTrend.labels,
                    datasets: [{
                        data: chartData.activityTrend.sessions,
                        borderColor: '#4F46E5',
                        backgroundColor: 'rgba(79, 70, 229, 0.12)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4F46E5',
                        pointBorderWidth: 2,
                    }],
                },
                options: defaultOptions,
            });

            new Chart(document.getElementById('popular-books-chart'), {
                type: 'bar',
                data: {
                    labels: chartData.popularBooks.labels,
                    datasets: [{
                        data: chartData.popularBooks.sessions,
                        backgroundColor: ['#4F46E5', '#6366F1', '#818CF8', '#A5B4FC', '#C7D2FE'],
                        borderRadius: 10,
                    }],
                },
                options: { ...defaultOptions, indexAxis: window.innerWidth < 768 ? 'x' : 'y' },
            });

            new Chart(document.getElementById('active-readers-chart'), {
                type: 'bar',
                data: {
                    labels: chartData.activeReaders.labels,
                    datasets: [{
                        data: chartData.activeReaders.sessions,
                        backgroundColor: '#10B981',
                        borderRadius: 10,
                    }],
                },
                options: defaultOptions,
            });
        })();
    </script>
@endpush
