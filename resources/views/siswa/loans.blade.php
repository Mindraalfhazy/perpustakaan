@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-header bg-white fw-bold py-3">Riwayat Pinjaman Saya</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tgl Kembali Aktual</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td>{{ $loans->firstItem() + $loop->index }}</td>
                                <td>{{ $loan->book?->judul ?? '-' }}</td>
                                <td>{{ $loan->tgl_pinjam?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $loan->tgl_kembali?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $loan->tgl_dikembalikan?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $loan->status === 'dikembalikan' ? 'badge-soft-green' : ($loan->status === 'terlambat' ? 'badge-soft-red' : 'badge-soft-yellow') }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($loan->denda ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @if(in_array($loan->status, ['dipinjam', 'terlambat']))
                                        <form action="{{ route('siswa.loans.return', $loan->id) }}" method="POST" onsubmit="return confirm('Yakin buku ini sudah Anda kembalikan ke perpustakaan?')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Kembalikan</button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada riwayat pinjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $loans->links() }}
        </div>
    </div>
@endsection