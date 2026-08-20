@extends('layouts.app')

@section('title', __('messages.products_data'))
@section('page-title', __('messages.products_management'))
@section('page-subtitle', __('messages.products_description'))

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-gradient-blue text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.total_products') }}</h6>
                        <h3 class="mb-0">{{ $totalProducts }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-green text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.active_products') }}</h6>
                        <h3 class="mb-0">{{ $activeProducts }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-orange text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.low_stock_products') }}</h6>
                        <h3 class="mb-0">{{ $lowStockProducts }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-red text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.out_of_stock_products') }}</h6>
                        <h3 class="mb-0">{{ $outOfStockProducts }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-box"></i> <strong>{{ __('messages.products_list') }}</strong>
        </div>
        <div>
            <a href="{{ route('products.catalog', request()->all()) }}" target="_blank" class="btn btn-outline-primary me-2">
                <i class="bi bi-printer"></i> Cetak Katalog
            </a>
            @if(auth()->user()->hasPermission('create_products'))
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> {{ __('messages.add_product') }}
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.product_code') }}</th>
                        <th>{{ __('messages.product_name') }}</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.selling_price') }}</th>
                        <th>{{ __('messages.stock') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->code ?? '-' }}</td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                        </td>
                        <td>{{ $product->category ?? '-' }}</td>
                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td>
                            @if($product->stock <= 0)
                                <span class="badge bg-danger">{{ __('messages.out_of_stock') }}</span>
                            @elseif($product->stock <= $product->min_stock)
                                <span class="badge bg-warning text-dark">{{ $product->stock }}</span>
                            @else
                                <span class="badge bg-success">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $products->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2">{{ __('messages.no_products_data') }}</p>
            {{-- <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Produk Pertama
            </a> --}}
        </div>
        @endif
    </div>
</div>
@endsection
