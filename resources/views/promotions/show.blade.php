@extends('layouts.app')

@section('title', 'Detail Promosi')
@section('page-title', 'Detail Promosi')
@section('page-subtitle', 'Informasi lengkap tentang promosi')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <i class="bi bi-tag"></i> <strong>{{ $promotion->name }}</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <tr>
                        <th style="width: 20%;">Nama Promosi</th>
                        <td><strong>{{ $promotion->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Tipe Promosi</th>
                        <td>
                            @if($promotion->type == 'product_discount')
                                Diskon Produk Spesifik
                            @elseif($promotion->type == 'product_markup')
                                Markup/Kenaikan Harga Produk
                            @elseif($promotion->type == 'transaction_discount')
                                Diskon Transaksi (Global)
                            @elseif($promotion->type == 'category_discount')
                                Diskon Kategori
                            @elseif($promotion->type == 'buy_x_get_y')
                                Buy X Get Y
                            @endif
                        </td>
                    </tr>
                    @if($promotion->type == 'category_discount')
                    <tr>
                        <th>Kategori Target</th>
                        <td>{{ $promotion->category_name }}</td>
                    </tr>
                    @endif
                    @if(in_array($promotion->type, ['product_discount', 'product_markup', 'buy_x_get_y']))
                    <tr>
                        <th>Produk Target</th>
                        <td>{{ $promotion->product ? $promotion->product->name : '-' }}</td>
                    </tr>
                    @endif
                    @if($promotion->type == 'buy_x_get_y')
                    <tr>
                        <th>Produk Reward (Gratis)</th>
                        <td>{{ $promotion->rewardProduct ? $promotion->rewardProduct->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Reward</th>
                        <td>{{ $promotion->reward_qty }} pcs</td>
                    </tr>
                    @endif
                    @if($promotion->type == 'transaction_discount')
                    <tr>
                        <th>Minimal Belanja</th>
                        <td>Rp {{ number_format($promotion->min_spend, 0, ',', '.') }}</td>
                    </tr>
                    @else
                    <tr>
                        <th>Minimal Qty Pembelian</th>
                        <td>{{ $promotion->min_qty }} pcs</td>
                    </tr>
                    @endif
                    @if($promotion->type != 'buy_x_get_y')
                    <tr>
                        <th>Nilai Diskon/Markup</th>
                        <td>
                            @if($promotion->value_type == 'percentage')
                                {{ $promotion->value }}%
                            @else
                                Rp {{ number_format($promotion->value, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>Status</th>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if(!$promotion->is_active)
                                    <span class="badge bg-secondary">Tidak Aktif (Dimatikan)</span>
                                @elseif($promotion->isValid())
                                    <span class="badge bg-success">Aktif & Berlaku</span>
                                @else
                                    <span class="badge bg-secondary">Kedaluwarsa / Kuota Habis</span>
                                @endif

                                @if(auth()->user()->hasPermission('edit_promotions'))
                                <form action="{{ route('promotions.toggle', $promotion->id) }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    @method('PATCH')
                                    {{-- @if($promotion->is_active)
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.75rem;">Matikan Promo</button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success py-0" style="font-size: 0.75rem;">Aktifkan Promo</button>
                                    @endif --}}
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Masa Berlaku</th>
                        <td>
                            @if($promotion->start_date && $promotion->end_date)
                                {{ $promotion->start_date->format('d/m/Y H:i') }} - {{ $promotion->end_date->format('d/m/Y H:i') }}
                            @elseif($promotion->start_date)
                                Mulai: {{ $promotion->start_date->format('d/m/Y H:i') }} (Tanpa Batas Akhir)
                            @elseif($promotion->end_date)
                                Hingga: {{ $promotion->end_date->format('d/m/Y H:i') }}
                            @else
                                Tanpa Batas Waktu
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Kode Voucher</th>
                        <td>{{ $promotion->promo_code ? $promotion->promo_code : 'Tidak ada (Otomatis)' }}</td>
                    </tr>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td>{{ $promotion->payment_method ? $promotion->payment_method : 'Semua Metode' }}</td>
                    </tr>
                    <tr>
                        <th>Kuota Pemakaian</th>
                        <td>
                            @if($promotion->usage_limit)
                                <span class="badge bg-info text-dark">{{ $promotion->used_count }} / {{ $promotion->usage_limit }} Terpakai</span>
                            @else
                                <span class="badge bg-info text-dark">{{ $promotion->used_count }} Terpakai (Tanpa Batas)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $promotion->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate</th>
                        <td>{{ $promotion->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('promotions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <div>
                @if(auth()->user()->hasPermission('edit_promotions'))
                <a href="{{ route('promotions.edit', $promotion->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Promo
                </a>
                @endif
                @if(auth()->user()->hasPermission('delete_promotions'))
                <form action="{{ route('promotions.destroy', $promotion->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus promo ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-clock-history"></i> <strong>Riwayat Transaksi</strong>
    </div>
    <div class="card-body">
        @if($promotion->sales()->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Invoice</th>
                        <th>Kasir</th>
                        <th>Total Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promotion->sales()->orderBy('created_at', 'desc')->get() as $sale)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                        <td>
                            @if(auth()->user()->hasPermission('view_sales'))
                            <a href="{{ route('sales.show', $sale->id) }}" class="text-primary fw-bold text-decoration-none">
                                {{ $sale->invoice_number }}
                            </a>
                            @else
                                {{ $sale->invoice_number }}
                            @endif
                        </td>
                        <td>{{ $sale->createdBy ? $sale->createdBy->name : '-' }}</td>
                        <td>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-3 text-muted">
            <i class="bi bi-inbox fs-2"></i>
            <p class="mt-2">Belum ada riwayat transaksi yang menggunakan promo ini.</p>
        </div>
        @endif
    </div>
</div>
@endsection
