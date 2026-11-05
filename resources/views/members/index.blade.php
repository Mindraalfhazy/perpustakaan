@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="card content-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau NISN..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary">Cari</button>
                </form>
                <a href="{{ route('admin.members.create') }}" class="btn app-btn-primary d-flex align-items-center gap-2">
                    <i data-feather="plus" style="width: 16px; height: 16px;"></i> Tambah Anggota
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $members->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($member->user?->profile_photo_url)
                                            <img src="{{ $member->user->profile_photo_url }}" alt="{{ $member->nama }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover;">
                                        @else
                                            <span class="avatar-circle" style="width:38px;height:38px;">{{ $member->user?->initials ?? strtoupper(substr($member->nama, 0, 1)) }}</span>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $member->nama }}</div>
                                            @if ($member->user?->isAdmin())
                                                <div class="small text-muted">NIK: {{ $member->user->masked_nik }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->nisn }}</td>
                                <td><span class="badge badge-soft-gray">{{ $member->kelas }}</span></td>
                                <td>{{ $member->email ?: '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-sm btn-outline-primary" style="padding: 6px 10px; border-radius: 8px;">
                                            <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                                        </a>
                                        <form action="{{ route('admin.members.destroy', $member) }}" method="POST" onsubmit="return confirm('Yakin hapus anggota ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" style="padding: 6px 10px; border-radius: 8px;">
                                                <i data-feather="trash" style="width: 16px; height: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data anggota.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $members->links() }}
        </div>
    </div>
@endsection
