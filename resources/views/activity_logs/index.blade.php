@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas Sistem')
@section('page-subtitle', 'Pantau semua perubahan data yang dilakukan oleh pengguna')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-clock-history"></i> <strong>Riwayat Aktivitas</strong>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('activity-logs.index') }}" method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="action" class="form-select">
                        <option value="">Semua Aksi</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created (Ditambah)</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated (Diubah)</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted (Dihapus)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="module" class="form-control" placeholder="Filter Modul (Cth: Product, User)" value="{{ request('module') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </div>
        </form>

        @if($logs->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>ID Data</th>
                        <th>Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>
                            {{ $log->created_at->format('d M Y H:i') }}
                            <br><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($log->user)
                                <strong>{{ $log->user->name }}</strong>
                            @else
                                <em>Sistem / Guest</em>
                            @endif
                        </td>
                        <td>
                            @if($log->action == 'created')
                                <span class="badge bg-success">Tambah</span>
                            @elseif($log->action == 'updated')
                                <span class="badge bg-warning text-dark">Ubah</span>
                            @elseif($log->action == 'deleted')
                                <span class="badge bg-danger">Hapus</span>
                            @else
                                <span class="badge bg-secondary">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $modelName = class_basename($log->model_type);
                            @endphp
                            {{ $modelName }}
                        </td>
                        <td>#{{ $log->model_id }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                <i class="bi bi-eye"></i> Lihat Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
        @else
        <div class="alert alert-info text-center">
            Belum ada catatan aktivitas yang terekam.
        </div>
        @endif
    </div>
</div>

@push('modals')
@if($logs->count() > 0)
    @foreach($logs as $log)
        @php
            $modelName = class_basename($log->model_type);
        @endphp
        <!-- Modal Detail -->
        <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Perubahan Data ({{ $modelName }} #{{ $log->model_id }})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($log->action == 'updated')
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Data Lama:</h6>
                                    <pre class="bg-light p-2 rounded" style="font-size: 12px; max-height: 400px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                <div class="col-md-6">
                                    <h6>Data Baru:</h6>
                                    <pre class="bg-light p-2 rounded" style="font-size: 12px; max-height: 400px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @elseif($log->action == 'created')
                            <h6>Data Ditambahkan:</h6>
                            <pre class="bg-light p-2 rounded" style="font-size: 12px; max-height: 400px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                        @elseif($log->action == 'deleted')
                            <h6>Data Dihapus:</h6>
                            <pre class="bg-light p-2 rounded" style="font-size: 12px; max-height: 400px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                        @endif
                        
                        <hr>
                        <small class="text-muted">IP Address: {{ $log->ip_address }} | User Agent: {{ Str::limit($log->user_agent, 50) }}</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endpush

@endsection
