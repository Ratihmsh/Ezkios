@extends('layouts.app')

@section('title', __('messages.sales_data'))
@section('page-title', __('messages.sales_management'))
@section('page-subtitle', __('messages.sales_description'))

@section('content')

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-gradient-blue text-white shadow-sm h-100 border-0">
            <div class="card-body text-center">
                <h6 class="opacity-75">{{ __('messages.sales_today') }}</h6>
                <h4 class="fw-bold">Rp {{ number_format($salesToday, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-green text-white shadow-sm h-100 border-0">
            <div class="card-body text-center">
                <h6 class="opacity-75">{{ __('messages.total_sales') }}</h6>
                <h4 class="fw-bold">Rp {{ number_format($totalSales, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-orange text-white shadow-sm h-100 border-0">
            <div class="card-body text-center">
                <h6 class="opacity-75">{{ __('messages.total_unpaid') }}</h6>
                <h4 class="fw-bold">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-info text-white shadow-sm h-100 border-0">
            <div class="card-body text-center">
                <h6 class="opacity-75">{{ __('messages.total_transactions') }}</h6>
                <h4 class="fw-bold">{{ number_format($totalTransactions, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-arrow-up-circle"></i> <strong>{{ __('messages.sales_list') }}</strong>
        </div>
        <div>
            <a href="{{ route('settlements.index') }}" class="btn btn-warning me-2">
                <i class="bi bi-box-arrow-in-down"></i> {{ __('messages.financial_closing') }}
            </a>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> {{ __('messages.add_sale') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($sales->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.invoice_no') }}</th>
                        <th>{{ __('messages.items_purchased') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.total') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $sale->invoice_number }}</strong></td>
                        <td>
                            <ul class="mb-0 list-unstyled" style="font-size: 0.9em;">
                                @foreach($sale->items as $item)
                                    <li>- {{ $item->product->name ?? 'Produk Dihapus' }} (x{{ $item->quantity }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $sale->payment_status_color }}">
                                @if($sale->payment_status === 'paid') {{ __('messages.paid') }}
                                @elseif($sale->payment_status === 'partial') {{ __('messages.partial') }}
                                @else {{ __('messages.unpaid') }} @endif
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($sale->payment_status !== 'paid')
                            {{-- <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a> --}}
                            @endif
                            {{-- <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                @if($sale->payment_status !== 'paid')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus penjualan ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </form> --}}
                            <a href="{{ route('sales.print', $sale) }}" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $sales->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2">{{ __('messages.no_sales_data') }}</p>
            {{-- <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Penjualan Pertama
            </a> --}}
        </div>
        @endif
    </div>
</div>
@endsection
