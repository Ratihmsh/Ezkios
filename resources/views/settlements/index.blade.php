@extends('layouts.app')

@section('title', 'Tutup Buku Kasir (Settlement)')
@section('page-title', 'Tutup Buku Kasir')
@section('page-subtitle', 'Manajemen data tutup buku kasir harian')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">Setoran Kasir Harian</h4>
                        <p class="mb-0">Pastikan uang fisik di laci sesuai dengan catatan sebelum menekan tombol Tutup Buku.</p>
                    </div>
                    @if($totalSales > 0)
                        <form action="{{ route('settlements.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-lg fw-bold" onclick="return confirm('Apakah Anda yakin uang fisik sudah sesuai? Transaksi akan disetor ke Laporan Keuangan.')">
                                <i class="bi bi-box-arrow-in-down"></i> Tutup Buku & Setorkan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Summary Cards -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Transaksi Belum Disetor</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSales }} Penjualan</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Omset (Kotor)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Uang Riil (Harus Ada di Laci/Rek)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                            @if($totalUnpaid > 0)
                                <small class="text-danger">Piutang (Belum Lunas): Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Breakdown Metode Pembayaran -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rincian Uang Riil (Per Metode)</h6>
                </div>
                <div class="card-body">
                    @if(count($paymentMethods) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($paymentMethods as $method => $amount)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $method }}
                                    <span class="badge bg-primary rounded-pill" style="font-size: 14px;">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted mb-0">Belum ada uang masuk.</p>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-clipboard-check"></i> Lakukan Koreksi Stok (Opname)
                        </a>
                        <small class="text-muted d-block mt-1 text-center">Lakukan koreksi stok jika ada selisih barang fisik sebelum tutup buku.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Transaksi Belum Disetor -->
        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Penjualan Belum Disetor</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>No. Nota</th>
                                    <th>Total Tagihan</th>
                                    <th>Uang Dibayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unsettledSales as $sale)
                                    <tr>
                                        <td>{{ $sale->created_at->format('H:i') }}</td>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                        <td>
                                            @if($sale->paid_amount > 0)
                                                Rp {{ number_format($sale->paid_amount - $sale->change_amount, 0, ',', '.') }}
                                                <small class="d-block text-muted">via {{ $sale->payment_method }}</small>
                                            @else
                                                <span class="text-danger">0</span>
                                            @endif
                                        </td>
                                        <td>{!! $sale->payment_status_label !!}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada transaksi yang menunggu disetor. Anda bisa beristirahat!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
