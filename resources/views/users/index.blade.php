@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')
@section('page-subtitle', 'Kelola semua user yang terdaftar')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-people"></i> <strong>Daftar User</strong>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <!-- Ambil dari relasi langsung (role) -->
                            <span class="badge bg-primary">{{ $user->role->display_name ?? 'Tidak ada role' }}</span>
                            <!-- Atau dari relasi many-to-many (roles) -->
                            @foreach($user->roles as $role)
                                @if($role->id != $user->role_id)
                                <span class="badge bg-secondary">{{ $role->display_name }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            {{-- @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $users->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-people" style="font-size: 3rem;"></i>
            <p class="mt-2">Belum ada user terdaftar.</p>
        </div>
        @endif
    </div>
</div>
@endsection
