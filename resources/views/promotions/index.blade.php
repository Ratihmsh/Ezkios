@extends('layouts.app')

@section('title', 'Manajemen Promosi')
@section('page-title', 'Daftar Promosi & Diskon')
@section('page-subtitle', 'Kelola semua promo, diskon, dan kenaikan harga')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-tags"></i> <strong>Daftar Promosi</strong>
        </div>
        @if(auth()->user()->hasPermission('create_promotions'))
        <a href="{{ route('promotions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Promosi
        </a>
        @endif
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Promo</th>
                        <th>Tipe</th>
                        <th>Produk Target</th>
                        <th>Syarat (Qty/Nominal)</th>
                        <th>Nilai (Diskon/Markup)</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promo)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $promo->name }}</td>
                        <td>
                            @if($promo->type == 'product_discount')
                                <span class="badge bg-success">Diskon Produk</span>
                            @elseif($promo->type == 'product_markup')
                                <span class="badge bg-danger">Markup Produk</span>
                            @elseif($promo->type == 'category_discount')
                                <span class="badge bg-primary">Diskon Kategori</span>
                            @elseif($promo->type == 'buy_x_get_y')
                                <span class="badge bg-warning text-dark">Buy X Get Y</span>
                            @else
                                <span class="badge bg-info text-dark">Diskon Global (Transaksi)</span>
                            @endif
                        </td>
                        <td>
                            @if($promo->type == 'category_discount')
                                Kategori: {{ $promo->category_name }}
                            @elseif($promo->type == 'transaction_discount')
                                -
                            @else
                                {{ $promo->product ? $promo->product->name : '-' }}
                            @endif
                        </td>
                        <td>
                            @if($promo->type == 'transaction_discount')
                                Min Belanja: Rp {{ number_format($promo->min_spend, 0, ',', '.') }}
                            @else
                                Min Qty: {{ $promo->min_qty }}
                            @endif
                        </td>
                        <td>
                            @if($promo->type == 'buy_x_get_y')
                                Gratis {{ $promo->reward_qty }}x {{ $promo->rewardProduct ? $promo->rewardProduct->name : '' }}
                            @elseif($promo->value_type == 'percentage')
                                {{ $promo->value }}%
                            @else
                                Rp {{ number_format($promo->value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td>
                            @if($promo->start_date && $promo->end_date)
                                {{ $promo->start_date->format('d/m/Y H:i') }} - {{ $promo->end_date->format('d/m/Y H:i') }}
                            @elseif($promo->start_date)
                                Mulai: {{ $promo->start_date->format('d/m/Y H:i') }} (Tanpa Batas)
                            @elseif($promo->end_date)
                                Sampai: {{ $promo->end_date->format('d/m/Y H:i') }}
                            @else
                                Tanpa Batas Waktu
                            @endif
                        </td>
                        <td>
                            @if(!$promo->is_active)
                                <span class="badge bg-secondary">Tidak Aktif (Dimatikan)</span>
                            @elseif($promo->isValid())
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Kadaluarsa</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->hasPermission('view_promotions'))
                            <a href="{{ route('promotions.show', $promo->id) }}" class="btn btn-sm btn-info text-black" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data promosi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
