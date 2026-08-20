@extends('layouts.app')

@section('title', 'Detail Produk')
@section('page-title', 'Detail Produk')
@section('page-subtitle', 'Informasi lengkap produk')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" class="img-fluid rounded" alt="{{ $product->name }}" style="max-height: 300px; object-fit: contain;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 300px;">
                        <i class="bi bi-box" style="font-size: 5rem; color: #ccc;"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <h4 class="mb-3">{{ $product->name }}</h4>
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Kode Produk</th>
                        <td>{{ $product->code ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $product->category }}</td>
                    </tr>
                    <tr>
                        <th>Merek/Brand</th>
                        <td>{{ $product->brand ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Harga Beli</th>
                        <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Harga Jual</th>
                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Stok Saat Ini</th>
                        <td>
                            <span class="badge {{ $product->stock <= $product->min_stock ? 'bg-danger' : 'bg-success' }}">
                                {{ $product->stock }} {{ $product->unit }}
                            </span>
                            @if($product->stock <= $product->min_stock)
                                <small class="text-danger ms-2"><i class="bi bi-exclamation-triangle"></i> Stok menipis!</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Minimal Stok</th>
                        <td>{{ $product->min_stock }} {{ $product->unit }}</td>
                    </tr>
                    <tr>
                        <th>Total Terjual</th>
                        <td>{{ $product->saleItems->sum('quantity') }} {{ $product->unit }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-primary">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if($product->description)
                <div class="mt-4">
                    <h5>Deskripsi Produk</h5>
                    <p class="text-muted">{{ $product->description }}</p>
                </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit Produk</a>
                    {{-- <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                            <i class="bi bi-trash"></i> Hapus Produk
                        </button>
                    </form> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-cart-check"></i> <strong>Riwayat Pembelian Barang</strong>
    </div>
    <div class="card-body">
        @if($product->purchaseItems->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Faktur</th>
                        <th>Supplier</th>
                        <th>Harga Beli</th>
                        <th>Jml Pembelian</th>
                        <th>Sisa Stok (FIFO)</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->purchaseItems as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('purchases.show', $item->purchase_id) }}">{{ $item->purchase->invoice_number }}</a>
                        </td>
                        <td>{{ $item->purchase->supplier->name ?? '-' }}</td>
                        <td>Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>
                            @if($item->remaining_quantity > 0)
                                <span class="badge bg-success">{{ $item->remaining_quantity }}</span>
                            @else
                                <span class="badge bg-secondary">Habis</span>
                            @endif
                        </td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-3 text-muted">
            <i class="bi bi-inbox fs-2"></i>
            <p class="mt-2">Belum ada riwayat pembelian untuk produk ini.</p>
        </div>
        @endif
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <i class="bi bi-bag-check"></i> <strong>Riwayat Penjualan Barang</strong>
    </div>
    <div class="card-body">
        @if($product->saleItems->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Faktur</th>
                        <th>Harga Jual</th>
                        <th>Diskon</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->saleItems as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->sale->sale_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('sales.show', $item->sale_id) }}">{{ $item->sale->invoice_number }}</a>
                        </td>
                        <td>Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->discount, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-3 text-muted">
            <i class="bi bi-inbox fs-2"></i>
            <p class="mt-2">Belum ada riwayat penjualan untuk produk ini.</p>
        </div>
        @endif
    </div>
</div>
@endsection
