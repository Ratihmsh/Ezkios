@extends('layouts.app')

@section('title', 'Data Supplier')
@section('page-title', 'Manajemen Supplier')
@section('page-subtitle', 'Kelola data pemasok/supplier toko')

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total Supplier</h6>
                        <h3 class="mb-0">{{ $totalSuppliers }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Supplier Aktif</h6>
                        <h3 class="mb-0">{{ $activeSuppliers }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Supplier Non-Aktif</h6>
                        <h3 class="mb-0">{{ $inactiveSuppliers }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-person-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-truck"></i> <strong>Daftar Supplier</strong>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Supplier
        </a>
    </div>
    <div class="card-body">
        @if($suppliers->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Supplier</th>
                        <th>Kontak</th>
                        <th>Kota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $supplier->code ?? '-' }}</td>
                        <td>
                            <strong>{{ $supplier->name }}</strong>
                            @if($supplier->contact_person)
                            <br><small class="text-muted">CP: {{ $supplier->contact_person }}</small>
                            @endif
                        </td>
                        <td>
                            @if($supplier->phone)
                            <div><i class="bi bi-telephone"></i> {{ $supplier->phone }}</div>
                            @endif
                            @if($supplier->email)
                            <div><i class="bi bi-envelope"></i> {{ $supplier->email }}</div>
                            @endif
                        </td>
                        <td>{{ $supplier->city ?? '-' }}</td>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            {{-- <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a> --}}
                            {{-- <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus supplier ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $suppliers->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2">Belum ada data supplier.</p>
            {{-- <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Supplier Pertama
            </a> --}}
        </div>
        @endif
    </div>
</div>
@endsection
