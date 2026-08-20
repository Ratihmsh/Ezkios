@extends('layouts.app')

@section('title', 'Detail Supplier')
@section('page-title', 'Detail Supplier')
@section('page-subtitle', 'Informasi lengkap supplier/pemasok')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-truck"></i> <strong>{{ $supplier->name }}</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <tr>
                        <th style="width: 20%;">Kode Supplier</th>
                        <td>{{ $supplier->code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nama Supplier</th>
                        <td><strong>{{ $supplier->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $supplier->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $supplier->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kota</th>
                        <td>{{ $supplier->city ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kode Pos</th>
                        <td>{{ $supplier->postal_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kontak Person</th>
                        <td>
                            @if($supplier->contact_person)
                                {{ $supplier->contact_person }}
                                @if($supplier->contact_person_phone)
                                    <br><small class="text-muted">HP: {{ $supplier->contact_person_phone }}</small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>{{ $supplier->notes ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Total Pembelian</th>
                        <td>
                            <span class="badge bg-info">{{ $supplier->purchases()->count() }} transaksi</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate</th>
                        <td>{{ $supplier->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <div>
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Supplier
                </a>
                {{-- <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus supplier ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form> --}}
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-cart-check"></i> <strong>Riwayat Transaksi</strong>
    </div>
    <div class="card-body">
        @if($supplier->purchases->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Faktur</th>
                        <th>Total Item</th>
                        <th>Total Pembelian</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplier->purchases as $purchase)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}">{{ $purchase->invoice_number }}</a>
                        </td>
                        <td>{{ $purchase->items->sum('quantity') ?? 0 }}</td>
                        <td>Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</td>
                        <td>
                            @if($purchase->payment_status === 'paid')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($purchase->payment_status === 'partial')
                                <span class="badge bg-warning text-dark">Sebagian</span>
                            @else
                                <span class="badge bg-danger">Belum Dibayar</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-3 text-muted">
            <i class="bi bi-inbox fs-2"></i>
            <p class="mt-2">Belum ada riwayat transaksi dengan supplier ini.</p>
        </div>
        @endif
    </div>
</div>
@endsection
