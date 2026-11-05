@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><strong>Nama Siswa:</strong> {{ $loan->member->nama }}</div>
                <div class="col-md-6"><strong>Judul Buku:</strong> {{ $loan->book->judul }}</div>
                <div class="col-md-6"><strong>Tanggal Pinjam:</strong> {{ $loan->tgl_pinjam->format('d/m/Y') }}</div>
                <div class="col-md-6"><strong>Tanggal Kembali:</strong> {{ $loan->tgl_kembali->format('d/m/Y') }}</div>
                <div class="col-md-6"><strong>Tanggal Dikembalikan:</strong> {{ $loan->tgl_dikembalikan?->format('d/m/Y') ?? '-' }}</div>
                <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($loan->status) }}</div>
                <div class="col-md-6"><strong>Denda:</strong> Rp {{ number_format($loan->denda, 0, ',', '.') }}</div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.loans.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
@endsection
