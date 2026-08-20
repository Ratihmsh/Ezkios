@extends('layouts.app')

@section('title', 'Dashboard - EZKIOS')
@section('page-title', __('messages.dashboard'))
@section('page-subtitle', 'Papan Pengumuman & Informasi Operasional')

@section('content')

<!-- 1. NEWS EVENT (Pengumuman) -->
@if(isset($newsEvents) && $newsEvents->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-megaphone-fill text-primary me-2"></i> Papan Pengumuman</h5>
        @foreach($newsEvents as $news)
        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0 align-items-center">
                @if($news->image)
                <div class="col-md-3">
                    <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid rounded-start h-100 object-fit-cover" alt="Poster" style="min-height: 150px;">
                </div>
                @endif
                <div class="col-md-{{ $news->image ? '9' : '12' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="card-title fw-bold text-dark mb-0">{{ $news->title }}</h4>
                            <span class="badge bg-light text-secondary border">
                                <i class="bi bi-calendar3"></i> {{ $news->start_date->format('d M') }} - {{ $news->end_date->format('d M Y') }}
                            </span>
                        </div>
                        <p class="card-text text-muted" style="white-space: pre-line;">{{ $news->content }}</p>

                        @if($news->youtube_link)
                            @php
                                // Convert youtube link to embed format
                                $embedUrl = $news->youtube_link;
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $news->youtube_link, $matches)) {
                                    $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                }
                            @endphp
                            <div class="ratio ratio-16x9 mb-3 mt-2 rounded overflow-hidden shadow-sm" style="max-width: 500px;">
                                <iframe src="{{ $embedUrl }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        @endif

                        @if($news->attachment)
                            <div class="mb-3">
                                <a href="{{ asset('storage/' . $news->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Unduh Lampiran
                                </a>
                            </div>
                        @endif

                        <p class="card-text"><small class="text-secondary"><i class="bi bi-person"></i> Diposting oleh: {{ $news->creator->name ?? 'Admin' }}</small></p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-light border shadow-sm text-center py-4 rounded-4" role="alert">
            <i class="bi bi-info-circle fs-3 text-secondary d-block mb-2"></i>
            Tidak ada pengumuman baru saat ini.
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- 2. Stok Menipis Alert -->
    @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
    <div class="col-md-6 mb-4">
        <div class="card h-100" style="border-left: 4px solid #ef4444;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: rgba(239, 68, 68, 0.05);">
                <h6 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> {{ __('messages.low_stock_warning') }}</h6>
                <span class="badge bg-danger rounded-pill px-3">{{ $lowStockProducts->count() }}</span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    @foreach($lowStockProducts as $product)
                    <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div>
                            <strong class="text-dark">{{ $product->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $product->category ?? __('messages.no_category') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger mb-1">{{ __('messages.stock') }}: {{ $product->stock }}</span>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">{{ __('messages.min_stock') }}: {{ $product->min_stock }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 text-end">
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-danger w-100">{{ __('messages.view_all') }}</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 3. Transaksi Jatuh Tempo (Belum Lunas) -->
    @if((isset($unpaidSales) && $unpaidSales->count() > 0) || (isset($unpaidPurchases) && $unpaidPurchases->count() > 0))
    <div class="col-md-6 mb-4">
        <div class="card h-100" style="border-left: 4px solid #f59e0b;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: rgba(245, 158, 11, 0.05);">
                <h6 class="mb-0 text-warning-emphasis"><i class="bi bi-clock-history me-2" style="color: #f59e0b;"></i> Jatuh Tempo (Belum Lunas)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($unpaidSales) && $unpaidSales->count() > 0)
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2 text-warning-emphasis fw-bold"><i class="bi bi-cart-dash"></i> Tagihan Penjualan</h6>
                        @foreach($unpaidSales as $sale)
                        <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div>
                                <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">{{ $sale->invoice_number }}</a>
                                <br>
                                <small class="text-muted">{{ $sale->customer_name ?? __('messages.public') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                                <br>
                                <small class="text-danger fw-bold">Jatuh tempo: {{ \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if(isset($unpaidPurchases) && $unpaidPurchases->count() > 0)
                    <div class="col-12 mb-3">
                        <h6 class="border-bottom pb-2 text-warning-emphasis fw-bold"><i class="bi bi-truck"></i> Tagihan Pembelian (Supplier)</h6>
                        @foreach($unpaidPurchases as $purchase)
                        <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">{{ $purchase->invoice_number }}</a>
                                <br>
                                <small class="text-muted">{{ $purchase->supplier->name ?? __('messages.unknown') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</span>
                                <br>
                                <small class="text-danger fw-bold">Jatuh tempo: {{ \Carbon\Carbon::parse($purchase->due_date)->format('d/m/Y') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
