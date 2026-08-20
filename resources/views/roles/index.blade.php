@extends('layouts.app')

@section('title', 'Manajemen Role')
@section('page-title', 'Manajemen Role')
@section('page-subtitle', 'Kelola semua role dan permission')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-shield-lock"></i> <strong>Daftar Role</strong>
        </div>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Role
        </a>
    </div>
    <div class="card-body">
        @if($roles->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Role</th>
                        <th>Deskripsi</th>
                        <th>Permissions</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $role->display_name }}</strong></td>
                        <td>{{ $role->description ?? '-' }}</td>
                        <td>
                            @foreach($role->permissions as $permission)
                            <span class="badge bg-info">{{ $permission->display_name }}</span>
                            @endforeach
                            @if($role->permissions->count() == 0)
                            <span class="badge bg-secondary">Tidak ada permission</span>
                            @endif
                        </td>
                        <td>
                            @if($role->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            {{-- <a href="{{ route('roles.permissions', $role) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-shield-check"></i>
                            </a> --}}
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus role ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $roles->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-shield-lock" style="font-size: 3rem;"></i>
            <p class="mt-2">Belum ada role terdaftar.</p>
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Role Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
