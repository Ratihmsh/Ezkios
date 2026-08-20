@extends('layouts.app')

@section('title', 'Koreksi Stok (Stock Opname)')
@section('page-title', 'Koreksi Stok')
@section('page-subtitle', 'Manajemen penyesuaian jumlah stok fisik barang')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-list-check"></i> Form Koreksi Stok Massal (Stock Opname)</h6>
            <button type="submit" form="stockOpnameForm" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Semua Koreksi</button>
        </div>
        <div class="card-body">
            <div class="alert alert-primary">
                <i class="bi bi-info-circle"></i> <strong>Tips:</strong> Kosongkan input <strong>Stok Nyata</strong> jika jumlah barang fisik sama dengan stok di sistem. Hanya baris yang diisi yang akan diproses oleh sistem.
            </div>

            <form action="{{ route('stock-adjustments.store') }}" method="POST" id="stockOpnameForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">#</th>
                                <th width="15%">Kode Barang</th>
                                <th width="25%">Nama Barang</th>
                                <th class="text-center" width="10%">Stok Sistem</th>
                                <th width="15%">Stok Nyata (Fisik)</th>
                                <th class="text-center" width="10%">Selisih</th>
                                <th width="20%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $product->code ?? '-' }}</td>
                                <td>{{ $product->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary fs-6" id="sys_stock_{{ $product->id }}">{{ $product->stock }}</span>
                                </td>
                                <td>
                                    <input type="number" name="actual_stocks[{{ $product->id }}]" class="form-control actual-stock-input" data-id="{{ $product->id }}" min="0" placeholder="Isi jika berbeda">
                                </td>
                                <td class="text-center" id="diff_col_{{ $product->id }}">
                                    <span class="text-muted">-</span>
                                </td>
                                <td>
                                    <input type="text" name="reasons[{{ $product->id }}]" class="form-control" placeholder="Alasan (hilang/rusak)">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat -->
    <div class="card shadow mb-4 border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-secondary"><i class="bi bi-clock-history"></i> Riwayat Koreksi Stok Terakhir</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th class="text-center">Sistem &rarr; Fisik</th>
                            <th class="text-center">Selisih</th>
                            <th>Keterangan</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                            <tr>
                                <td>{{ $adj->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $adj->product->name ?? 'Barang Dihapus' }}</td>
                                <td class="text-center">{{ $adj->old_stock }} &rarr; <strong>{{ $adj->new_stock }}</strong></td>
                                <td class="text-center">
                                    @if($adj->difference < 0)
                                        <span class="badge bg-danger">{{ $adj->difference }} (Hilang)</span>
                                    @elseif($adj->difference > 0)
                                        <span class="badge bg-success">+{{ $adj->difference }} (Lebih)</span>
                                    @else
                                        <span class="badge bg-secondary">0 (Sesuai)</span>
                                    @endif
                                </td>
                                <td>{{ $adj->reason ?? '-' }}</td>
                                <td>{{ $adj->createdBy->name ?? 'Sistem' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat koreksi stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.actual-stock-input');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const id = this.getAttribute('data-id');
                const sysStockElement = document.getElementById('sys_stock_' + id);
                const diffCol = document.getElementById('diff_col_' + id);
                const reasonInput = document.querySelector('input[name="reasons[' + id + ']"]');
                
                const sysStock = parseInt(sysStockElement.innerText || 0);
                
                if (this.value === '') {
                    diffCol.innerHTML = '<span class="text-muted">-</span>';
                    reasonInput.removeAttribute('required');
                    return;
                }
                
                const actualStock = parseInt(this.value);
                const diff = actualStock - sysStock;
                
                if (diff < 0) {
                    diffCol.innerHTML = `<span class="badge bg-danger">${diff}</span>`;
                    reasonInput.setAttribute('required', 'required');
                } else if (diff > 0) {
                    diffCol.innerHTML = `<span class="badge bg-success">+${diff}</span>`;
                    reasonInput.setAttribute('required', 'required');
                } else {
                    diffCol.innerHTML = `<span class="badge bg-secondary">0</span>`;
                    reasonInput.removeAttribute('required');
                }
            });
        });
    });
</script>
@endpush
