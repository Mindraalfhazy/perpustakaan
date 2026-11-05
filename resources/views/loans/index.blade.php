@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $lateFeePerDay = \App\Support\LibrarySetting::lateFeePerDay();
    @endphp

    <div class="card content-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="dipinjam" @selected($status === 'dipinjam')>Dipinjam</option>
                            <option value="dikembalikan" @selected($status === 'dikembalikan')>Dikembalikan</option>
                            <option value="terlambat" @selected($status === 'terlambat')>Terlambat</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama siswa" value="{{ $search }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary">Filter</button>
                    </div>
                </form>

                <a href="{{ route('admin.loans.create') }}" class="btn app-btn-primary d-flex align-items-center gap-2">
                    <i data-feather="plus" style="width: 16px; height: 16px;"></i> Catat Peminjaman
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Judul Buku</th>
                            <th>Jumlah Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($loans as $loan)
                            @php
                                $lateDays = max(0, now()->startOfDay()->diffInDays($loan->tgl_kembali, false) * -1);
                                $returnFine = $lateDays * $lateFeePerDay;

                                $isLateReturned = $loan->status === 'dikembalikan' && $loan->denda > 0;
                                $isLateActive = in_array($loan->status, ['dipinjam', 'terlambat'], true)
                                    && $loan->tgl_kembali->lt(today());
                            @endphp

                            <tr>
                                <td>{{ $loans->firstItem() + $loop->index }}</td>
                                <td>{{ $loan->member->nama }}</td>
                                <td>{{ $loan->book->judul }}</td>

                                <td class="text-center">
                                    <span class="badge bg-primary">
                                        {{ $loan->jumlah }}
                                    </span>
                                </td>

                                <td>{{ $loan->tgl_pinjam->format('d/m/Y') }}</td>
                                <td>{{ $loan->tgl_kembali->format('d/m/Y') }}</td>

                                <td>
                                    @if ($isLateReturned)
                                        <span class="badge badge-soft-red">
                                            Terlambat <span class="fw-normal">(Sudah Kembali)</span>
                                        </span>
                                    @elseif ($isLateActive)
                                        <span class="badge badge-soft-red">
                                            Terlambat <span class="fw-normal">(Belum Kembali)</span>
                                        </span>
                                    @elseif ($loan->status === 'dikembalikan')
                                        <span class="badge badge-soft-green">
                                            Dikembalikan
                                        </span>
                                    @else
                                        <span class="badge badge-soft-yellow">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    Rp{{ number_format($loan->denda ?: 0, 0, ',', '.') }}
                                </td>

                                <td>
                                    <div class="d-flex gap-2 flex-wrap">

                                        @if (in_array($loan->status, ['dipinjam', 'terlambat'], true))
                                            <a href="{{ route('admin.loans.edit', $loan) }}" class="btn btn-sm btn-outline-secondary">
                                                Edit
                                            </a>

                                            <button type="button"
                                                class="btn btn-sm btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#returnModal{{ $loan->id }}">
                                                Kembalikan
                                            </button>
                                        @endif

                                        <a href="{{ route('admin.loans.show', $loan) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Belum ada data peminjaman.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{ $loans->links() }}

        </div>
    </div>

    @foreach ($loans as $loan)
        @if (in_array($loan->status, ['dipinjam', 'terlambat'], true))
            @php
                $lateDays = max(0, now()->startOfDay()->diffInDays($loan->tgl_kembali, false) * -1);
                $returnFine = $lateDays * $lateFeePerDay;
            @endphp

            <div class="modal fade" id="returnModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                Konfirmasi Pengembalian
                            </h5>

                            <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Tutup">
                            </button>
                        </div>

                        <div class="modal-body">
                            <p><strong>Buku :</strong> {{ $loan->book->judul }}</p>
                            <p><strong>Jumlah :</strong> {{ $loan->jumlah }} Buku</p>
                            <p><strong>Anggota :</strong> {{ $loan->member->nama }}</p>
                            <p><strong>Tgl Kembali :</strong> {{ $loan->tgl_kembali->format('d/m/Y') }}</p>
                            <p><strong>Hari Ini :</strong> {{ now()->format('d/m/Y') }}</p>
                            <p><strong>Keterlambatan :</strong> {{ $lateDays }} hari</p>
                            <p><strong>Denda :</strong> Rp {{ number_format($returnFine, 0, ',', '.') }}</p>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                                Batalkan
                            </button>

                            <form action="{{ route('admin.loans.return', $loan) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Proses Pengembalian
                                </button>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
