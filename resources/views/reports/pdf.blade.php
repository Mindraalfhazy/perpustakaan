<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 26px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color: #111827; }
        .header-table,
        .info-table,
        .meta-table,
        .report-table,
        .footer-table { width: 100%; border-collapse: collapse; }
        .header-table { border-bottom: 3px solid #4F46E5; padding-bottom: 10px; margin-bottom: 12px; }
        .header-table td { vertical-align: top; }
        .logo-wrap { width: 76px; }
        .logo { width: 64px; height: 64px; object-fit: contain; }
        .school-name { font-size: 18px; font-weight: 700; color: #312E81; text-transform: uppercase; }
        .school-unit { font-size: 12px; font-weight: 700; color: #4F46E5; margin-top: 2px; }
        .school-address { font-size: 11px; color: #374151; margin-top: 4px; line-height: 1.5; }
        .info-panel {
            margin: 14px 0 12px;
            padding: 10px 12px;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
        }
        .info-table td { padding: 2px 4px; font-size: 10.5px; vertical-align: top; }
        .report-title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            margin: 14px 0 4px;
        }
        .report-subtitle { text-align: center; color: #4B5563; margin-bottom: 12px; }
        .meta-table { margin-bottom: 12px; }
        .meta-table td {
            padding: 7px 9px;
            border: 1px solid #D1D5DB;
            background: #F8FAFC;
            font-size: 10.5px;
        }
        .report-table th,
        .report-table td { border: 1px solid #9CA3AF; padding: 7px 8px; text-align: left; vertical-align: top; }
        .report-table th { background: #E0E7FF; color: #312E81; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary {
            margin-top: 14px;
            padding: 10px 12px;
            background: #EEF2FF;
            border: 1px solid #C7D2FE;
            color: #312E81;
        }
        .footer-table { margin-top: 22px; }
        .footer-table td { font-size: 11px; vertical-align: top; }
    </style>
</head>
<body>
    @php
        $school = config('perpustakaan.school');
        $logoPath = public_path($school['logo']);
        $logoSource = file_exists($logoPath) ? $logoPath : null;
        $periodLabel = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') . ' ' . $tahun;
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-wrap">
                @if ($logoSource)
                    <img src="{{ $logoSource }}" alt="Logo {{ $school['short_name'] }}" class="logo">
                @endif
            </td>
            <td>
                <div class="school-name">{{ $school['name'] }}</div>
                <div class="school-unit">{{ $school['library_name'] }}</div>
                <div class="school-address">
                    {{ $school['address'] }}, {{ $school['village'] }}, {{ $school['district'] }}, {{ $school['city'] }}, {{ $school['province'] }}
                </div>
            </td>
        </tr>
    </table>

    <div class="info-panel">
        <table class="info-table">
            <tr>
                <td style="width: 90px;"><strong>NPSN</strong></td>
                <td style="width: 10px;">:</td>
                <td>{{ $school['npsn'] }}</td>
                <td style="width: 135px;"><strong>Status Sekolah</strong></td>
                <td style="width: 10px;">:</td>
                <td>{{ $school['status'] }}</td>
            </tr>
            <tr>
                <td><strong>Bentuk Pendidikan</strong></td>
                <td>:</td>
                <td>{{ $school['education_form'] }}</td>
                <td><strong>Jenjang Pendidikan</strong></td>
                <td>:</td>
                <td>{{ $school['education_level'] }}</td>
            </tr>
        </table>
    </div>

    <div class="report-title">Laporan {{ $jenis }}</div>
    <div class="report-subtitle">Periode {{ $periodLabel }}</div>

    <table class="meta-table">
        <tr>
            <td><strong>Jenis Laporan:</strong> {{ $jenis }}</td>
            <td><strong>Periode:</strong> {{ $periodLabel }}</td>
            <td><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Judul Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Tgl Dikembalikan</th>
                <th>Status</th>
                <th class="text-right">Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $loan->member?->nama ?? '-' }}</td>
                    <td>{{ $loan->book?->judul ?? '-' }}</td>
                    <td>{{ $loan->tgl_pinjam?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $loan->tgl_kembali?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $loan->tgl_dikembalikan?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ ucfirst($loan->status) }}</td>
                    <td class="text-right">Rp {{ number_format($loan->denda ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada data laporan untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Total Data:</strong> {{ $loans->count() }} &nbsp;|&nbsp;
        <strong>Total Denda:</strong> Rp {{ number_format($loans->sum('denda'), 0, ',', '.') }}
    </div>

    <table class="footer-table">
        <tr>
            <td style="width: 55%;"></td>
            <td class="text-center">
                Bone, {{ now()->translatedFormat('d F Y') }}<br>
                Mengetahui,<br><br><br><br>
                <strong>Petugas Perpustakaan</strong>
            </td>
        </tr>
    </table>
</body>
</html>
