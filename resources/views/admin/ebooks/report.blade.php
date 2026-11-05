<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page { margin: 100px 32px 70px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .report-header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 10px;
        }
        .report-footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 11px;
        }
        .page-number:after { content: counter(page); }
        .brand-row { width: 100%; }
        .logo { width: 58px; height: 58px; object-fit: contain; }
        .school-title { font-size: 18px; font-weight: 700; color: #312E81; text-transform: uppercase; }
        .school-subtitle { font-size: 11px; color: #4b5563; line-height: 1.45; }
        .school-meta { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .summary-table,
        .detail-table { width: 100%; border-collapse: collapse; }
        .summary-card {
            width: 24%;
            display: inline-block;
            vertical-align: top;
            padding: 10px 12px;
            border-radius: 12px;
            background: #f5f7ff;
            border: 1px solid #c7d2fe;
            margin-right: 1%;
            margin-bottom: 12px;
        }
        .summary-label { color: #6b7280; font-size: 11px; margin-bottom: 6px; }
        .summary-value { font-weight: 700; }
        .filter-note { margin: 10px 0 16px; color: #4b5563; }
        .detail-table th,
        .detail-table td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        .detail-table th { background: #ede9fe; color: #4c1d95; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #6b7280; }
        .school-info-box {
            margin: 6px 0 14px;
            padding: 10px 12px;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            color: #334155;
            font-size: 11px;
            line-height: 1.5;
        }
        .signature-box {
            width: 220px;
            margin-left: auto;
            margin-top: 20px;
            text-align: center;
            line-height: 1.5;
        }
        .screen-toolbar { margin-bottom: 20px; }
        .screen-toolbar .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #6D28D9;
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            margin-right: 8px;
        }
        @media print {
            .screen-toolbar { display: none !important; }
        }
    </style>
</head>
<body>
    @php
        $school = config('perpustakaan.school');
        $resolvedLogoPath = public_path($school['logo']);
        $logoSource = $isPdf && file_exists($resolvedLogoPath) ? $resolvedLogoPath : asset($school['logo']);
    @endphp

    @unless ($isPdf)
        <div class="screen-toolbar">
            <a href="javascript:window.print()" class="btn">Cetak Laporan</a>
        </div>
    @endunless

    <div class="report-header">
        <table class="brand-row">
            <tr>
                <td style="width:64px;">
                    <img src="{{ $logoSource }}" alt="Logo {{ $school['short_name'] }}" class="logo">
                </td>
                <td>
                    <div class="school-title">{{ $school['name'] }}</div>
                    <div class="school-subtitle">{{ $school['library_name'] }} | {{ $school['address'] }}, {{ $school['district'] }}, {{ $school['city'] }}</div>
                    <div class="school-meta">NPSN {{ $school['npsn'] }} | Status {{ $school['status'] }} | Jenjang {{ $school['education_level'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-footer">
        <table style="width:100%;">
            <tr>
                <td>Dokumen laporan riwayat baca e-book</td>
                <td class="text-right">Halaman <span class="page-number"></span></td>
            </tr>
        </table>
    </div>

    <main>
        <div class="school-info-box">
            <strong>{{ $reportTitle }}</strong><br>
            Periode: {{ $periodLabel }}<br>
            Dibuat pada {{ $generatedAt->translatedFormat('d F Y H:i') }} oleh {{ $generatedBy->name }}<br>
            Lokasi sekolah: {{ $school['village'] }}, {{ $school['district'] }}, {{ $school['city'] }}, {{ $school['province'] }}
        </div>

        <div style="margin-bottom: 18px;">
            <div class="summary-card">
                <div class="summary-label">Periode</div>
                <div class="summary-value">{{ $periodLabel }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Sesi</div>
                <div class="summary-value">{{ $summary['total_sessions'] }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Pembaca Unik</div>
                <div class="summary-value">{{ $summary['unique_readers'] }}</div>
            </div>
            <div class="summary-card" style="margin-right:0;">
                <div class="summary-label">E-Book Teratas</div>
                <div class="summary-value">{{ $summary['top_book'] }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Siswa Teraktif</div>
                <div class="summary-value">{{ $summary['top_student'] }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Kelas Teraktif</div>
                <div class="summary-value">{{ $summary['top_class'] }}</div>
            </div>
        </div>

        <div class="filter-note">
            Filter aktif:
            siswa <strong>{{ $filters['student'] ?: 'semua' }}</strong>,
            buku <strong>{{ $filters['book'] ?: 'semua' }}</strong>,
            kelas <strong>{{ $filters['kelas'] ?: 'semua' }}</strong>,
            kategori <strong>{{ $filters['category'] ?: 'semua' }}</strong>,
            rentang <strong>{{ $filters['date_from'] }} s.d. {{ $filters['date_to'] }}</strong>.
        </div>

        <table class="detail-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Judul E-Book</th>
                    <th>Dibuka</th>
                    <th>Durasi</th>
                    <th>Perangkat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $log->user?->name ?? '-' }}</td>
                        <td>{{ $log->user?->displayClass() ?? '-' }}</td>
                        <td>
                            <strong>{{ $log->book?->judul ?? '-' }}</strong><br>
                            <span class="muted">{{ $log->book?->kategori ?? 'E-Book' }}</span>
                        </td>
                        <td>
                            {{ $log->opened_at?->format('d/m/Y H:i') ?? '-' }}<br>
                            <span class="muted">Terakhir: {{ $log->last_read_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </td>
                        <td>{{ $log->duration_label }}</td>
                        <td>{{ ucfirst((string) $log->device_type) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature-box">
            Bone, {{ $generatedAt->translatedFormat('d F Y') }}<br>
            Mengetahui,<br><br><br><br>
            <strong>Petugas Perpustakaan</strong>
        </div>
    </main>

    @unless ($isPdf)
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endunless
</body>
</html>
