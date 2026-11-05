@extends('layouts.app')

@section('title', $title ?? 'Kartu Anggota Siswa')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="content-card p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Kartu Anggota Siswa</h5>
                    <p class="text-muted mb-0 small">Terbitkan nomor kartu anggota perpustakaan per siswa atau sekaligus per kelas.</p>
                </div>

                <!-- Terbitkan sekaligus per kelas -->
                <form action="{{ route('admin.cards.bulk-issue') }}" method="POST" class="d-flex gap-2" onsubmit="return confirm('Terbitkan kartu untuk semua siswa di kelas ini yang belum punya kartu?');">
                    @csrf
                    <select name="kelas" class="form-select form-select-sm" required style="min-height: 38px;">
                        <option value="">Pilih kelas...</option>
                        @foreach ($kelasOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn app-btn-primary btn-sm" style="min-height: 38px; white-space: nowrap;">
                        <i data-feather="layers" style="width:16px;height:16px;"></i> Terbitkan per Kelas
                    </button>
                </form>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Filter -->
            <form action="{{ route('admin.cards.index') }}" method="GET" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau NISN...">
                </div>
                <div class="col-md-3">
                    <select name="kelas" class="form-select">
                        <option value="">Semua kelas</option>
                        @foreach ($kelasOptions as $opt)
                            <option value="{{ $opt }}" {{ $kelas === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
                @if ($search || $kelas)
                    <div class="col-md-2">
                        <a href="{{ route('admin.cards.index') }}" class="btn btn-light w-100">Reset</a>
                    </div>
                @endif
            </form>

            <!-- Tabel -->
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Status Kartu</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            @php $card = $member->user?->card; @endphp
                            <tr>
                                <td class="fw-semibold">{{ $member->nama }}</td>
                                <td>{{ $member->nisn ?? '-' }}</td>
                                <td>{{ $member->kelas ?? '-' }}</td>
                                <td>
                                    @if (! $member->user_id)
                                        <span class="badge badge-soft-gray">Belum ada akun user</span>
                                    @elseif ($card)
                                        <span class="badge badge-soft-green">{{ $card->number }}</span>
                                    @else
                                        <span class="badge badge-soft-yellow">Belum diterbitkan</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($member->user_id && ! $card)
                                        <form action="{{ route('admin.cards.issue', $member) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm app-btn-primary">
                                                Terbitkan
                                            </button>
                                        </form>
                                    @elseif ($card)
                                        <span class="text-muted small">Sudah terbit</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada data siswa yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</div>
@endsection