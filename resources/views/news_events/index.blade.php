@extends('layouts.app')

@section('title', 'Pengumuman - EZKIOS')
@section('page-title', 'Pengumuman (News/Event)')
@section('page-subtitle', 'Manajemen daftar pengumuman untuk dashboard')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom pt-4 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-megaphone text-primary"></i> Daftar Pengumuman</h5>
        <a href="{{ route('news-events.create') }}" class="btn btn-primary fw-bold text-white shadow-sm" style="border-radius: 8px;">
            <i class="bi bi-plus-lg"></i> Tambah Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Tanggal Tayang</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($newsEvents as $news)
                    <tr>
                        <td class="px-4 py-3">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">
                            <strong class="text-dark">{{ $news->title }}</strong>
                            <div class="text-muted small text-truncate" style="max-width: 200px;">{{ Str::limit(strip_tags($news->content), 30) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge bg-secondary rounded-pill">
                                <i class="bi bi-calendar"></i> {{ $news->start_date->format('d/m/Y') }} - {{ $news->end_date->format('d/m/Y') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($news->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('news-events.edit', $news->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('news-events.destroy', $news->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada pengumuman
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
