@extends('layouts.app')

@section('title', 'Detail Penjualan')
@section('page-title', 'Detail Penjualan')
@section('page-subtitle', 'Informasi lengkap penjualan')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-arrow-up-circle"></i> <strong>Detail Penjualan</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table">
                    <tr>
                        <th style="width: 35%;">No. Faktur</th>
                        <td><strong>{{ $sale->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal Penjualan</th>
                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <td>{{ $sale->customer_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. HP Pelanggan</th>
                        <td>{{ $sale->customer_phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td>
                            <span class="badge bg-{{ $sale->payment_status_color }}">
                                {{ $sale->payment_status_label }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td>{{ $sale->payment_method ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jatuh Tempo</th>
                        <td>{{ $sale->due_date ? $sale->due_date->format('d/m/Y') : '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table">
                    {{-- <tr>
                        <th style="width: 35%;">Total Harga</th>
                        <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Diskon</th>
                        <td>Rp {{ number_format($sale->discount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Pajak</th>
                        <td>Rp {{ number_format($sale->tax ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Ongkos Kirim</th>
                        <td>Rp {{ number_format($sale->shipping_cost ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th><strong>Grand Total</strong></th>
                        <td><strong>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <th>Jumlah Dibayar</th>
                        <td>Rp {{ number_format($sale->paid_amount ?? 0, 0, ',', '.') }}</td>
                    </tr> --}}
                    <tr>
                        <th>Dibuat Oleh</th>
                        <td>{{ $sale->createdBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>
                            <p>{{ $sale->notes ?? '-' }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- <div class="mb-3">
            <label class="fw-bold">Catatan</label>
            <p>{{ $sale->notes ?? '-' }}</p>
        </div> --}}

        <!-- Items -->
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-list"></i> <strong>Daftar Barang</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga Jual</th>
                                <th>Diskon</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $item->product->name }}
                                    <br><small class="text-muted">{{ $item->product->code ?? '-' }}</small>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->discount ?? 0, 0, ',', '.') }}</td>
                                <td><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total</th>
                                <th>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">Diskon</th>
                                <th>Rp {{ number_format($sale->discount ?? 0, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">Pajak</th>
                                <th>Rp {{ number_format($sale->tax ?? 0, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">Ongkos Kirim</th>
                                <th>Rp {{ number_format($sale->shipping_cost ?? 0, 0, ',', '.') }}</th>
                            </tr>
                            <tr class="table-primary">
                                <th colspan="5" class="text-end"><strong>Grand Total</strong></th>
                                <th><strong>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</strong></th>
                            </tr>
                            <tr class="table-success">
                                <th colspan="5" class="text-end"><strong>Sudah Dibayar</strong></th>
                                <th><strong>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</strong></th>
                            </tr>
                            @if($sale->payment_status === 'paid' && $sale->change_amount > 0)
                            <tr class="table-info">
                                <th colspan="5" class="text-end"><strong>Kembalian</strong></th>
                                <th><strong>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</strong></th>
                            </tr>
                            @elseif($sale->payment_status !== 'paid')
                            <tr class="table-danger">
                                <th colspan="5" class="text-end"><strong>Sisa Tagihan</strong></th>
                                <th><strong>Rp {{ number_format($sale->grand_total - $sale->paid_amount, 0, ',', '.') }}</strong></th>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($sale->payments && $sale->payments->count() > 0)
        <!-- Riwayat Pembayaran -->
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cash-coin"></i> <strong>Riwayat Pembayaran</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Nota</th>
                                <th>Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->payments as $index => $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                                <td>{{ $payment->payment_method ?? '-' }}</td>
                                <td>
                                    @if($payment->receipt_image)
                                        <a href="{{ asset('storage/' . $payment->receipt_image) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark-image"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $payment->createdBy->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <div>
                @if($sale->payment_status !== 'paid')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="bi bi-cash-stack"></i> Pelunasan
                </button>
                {{-- <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a> --}}
                @endif
                <a href="{{ route('sales.print', $sale) }}" class="btn btn-secondary" target="_blank">
                    <i class="bi bi-printer"></i> Print
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@if($sale->payment_status !== 'paid')
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('sales.payments.store', $sale) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Pelunasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tagihan</label>
                        <input type="text" class="form-control fw-bold" readonly value="Rp {{ number_format($sale->grand_total - $sale->paid_amount, 0, ',', '.') }}">
                        <input type="hidden" id="raw_remaining_balance_show" value="{{ intval($sale->grand_total - $sale->paid_amount) }}">
                    </div>
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Jumlah Pembayaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="payment_amount" id="payment_amount_show" class="form-control nominal-input" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sisa Tagihan</label>
                        <input type="text" id="display_new_balance_show" class="form-control text-danger fw-bold" readonly value="Rp 0">
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Pilih Metode</option>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm }}">{{ $pm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Jatuh Tempo</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ $sale->due_date ? \Carbon\Carbon::parse($sale->due_date)->format('Y-m-d') : '' }}">
                        <small class="text-muted">Kosongkan jika sudah lunas atau tidak ada perubahan.</small>
                    </div>
                    <div class="mb-3">
                        <label for="receipt_image" class="form-label">Foto Nota Bukti Pembayaran (Opsional)</label>
                        <input type="file" name="receipt_image" id="receipt_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format nominal input in modal
    const nominalInputs = document.querySelectorAll('.nominal-input');

    function formatRibuan(angka) {
        if (angka === null || angka === undefined) return '0';
        let number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    nominalInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            this.value = formatRibuan(this.value);
            calculateNewBalanceShow();
        });
    });

    function calculateNewBalanceShow() {
        const rawBalanceStr = document.getElementById('raw_remaining_balance_show')?.value || '0';
        let rawBalance = parseInt(rawBalanceStr.replace(/[^0-9]/g, ''), 10) || 0;

        const paymentInput = document.getElementById('payment_amount_show');
        if (!paymentInput) return;

        let paymentAmount = parseInt(paymentInput.value.replace(/[^0-9]/g, ''), 10) || 0;

        let newBalance = rawBalance - paymentAmount;
        if (newBalance < 0) newBalance = 0; // Prevent negative display if they overpay

        const displayNewBalance = document.getElementById('display_new_balance_show');
        if (displayNewBalance) {
            displayNewBalance.value = 'Rp ' + formatRibuan(newBalance);
        }
    }

    // Call once on load in case there's an initial value
    calculateNewBalanceShow();
});
</script>
@endpush
