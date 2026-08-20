@extends('layouts.app')

@section('title', __('messages.purchases_data'))
@section('page-title', __('messages.purchases_management'))
@section('page-subtitle', __('messages.purchases_description'))

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-gradient-blue text-white shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.total_purchases') }}</h6>
                        <h3 class="mb-0">{{ $totalPurchases }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-orange text-white shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.unpaid_purchases_status') }}</h6>
                        <h3 class="mb-0">{{ $unpaidPurchases }}</h3>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-green text-white shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.total_spend') }}</h6>
                        <h4 class="mb-0">Rp {{ number_format($totalSpend, 0, ',', '.') }}</h4>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-gradient-red text-white shadow-sm h-100 border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">{{ __('messages.total_debt') }}</h6>
                        <h4 class="mb-0">Rp {{ number_format($totalDebt, 0, ',', '.') }}</h4>
                    </div>
                    <div class="fs-1 text-white-50">
                        <i class="bi bi-journal-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-arrow-down-circle"></i> <strong>{{ __('messages.purchases_list') }}</strong>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> {{ __('messages.add_purchase') }}
        </a>
    </div>
    <div class="card-body">
        @if($purchases->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.invoice_no') }}</th>
                        <th>{{ __('messages.supplier') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.total') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $purchase->invoice_number }}</strong></td>
                        <td>{{ $purchase->supplier->name ?? '-' }}</td>
                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $purchase->payment_status_color }}">
                                @if($purchase->payment_status === 'paid') {{ __('messages.paid') }}
                                @elseif($purchase->payment_status === 'partial') {{ __('messages.partial') }}
                                @else {{ __('messages.unpaid') }} @endif
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($purchase->payment_status !== 'paid')
                            {{-- <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal" data-id="{{ $purchase->id }}" data-invoice="{{ $purchase->invoice_number }}" data-balance="{{ intval($purchase->grand_total - $purchase->paid_amount) }}" data-duedate="{{ $purchase->due_date ? \Carbon\Carbon::parse($purchase->due_date)->format('Y-m-d') : '' }}" title="Pelunasan">
                                <i class="bi bi-cash-stack"></i>
                            </button> --}}
                            {{-- <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a> --}}
                            @endif
                            <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $purchases->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2">{{ __('messages.no_purchases_data') }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="paymentForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Bayar / Pelunasan <span id="paymentInvoice"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sisa Tagihan Saat Ini</label>
                        <input type="text" id="display_remaining_balance" class="form-control fw-bold" readonly>
                        <!-- Hidden input to store raw balance for calculation -->
                        <input type="hidden" id="raw_remaining_balance">
                    </div>
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Jumlah Pembayaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="payment_amount" id="payment_amount" class="form-control nominal-input" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sisa Tagihan Setelah Pembayaran</label>
                        <input type="text" id="display_new_balance" class="form-control text-danger fw-bold" readonly>
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
                        <label for="due_date" class="form-label">Jatuh Tempo (Sisa Tagihan)</label>
                        <input type="date" name="due_date" id="due_date" class="form-control">
                        <small class="text-muted">Kosongkan jika sudah lunas atau tidak ada perubahan.</small>
                    </div>
                    <div class="mb-3">
                        <label for="receipt_image" class="form-label">Foto Nota <span class="text-danger">*</span></label>
                        <input type="file" name="receipt_image" id="receipt_image" class="form-control" accept="image/*" required>
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format nominal input
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
        input.addEventListener('input', function(e) {
            this.value = formatRibuan(this.value);
            calculateNewBalance();
        });
    });

    function calculateNewBalance() {
        const rawBalanceStr = document.getElementById('raw_remaining_balance')?.value || '0';
        let rawBalance = parseInt(rawBalanceStr.replace(/[^0-9]/g, ''), 10) || 0;

        const paymentInput = document.getElementById('payment_amount');
        if (!paymentInput) return;

        let paymentAmount = parseInt(paymentInput.value.replace(/[^0-9]/g, ''), 10) || 0;

        let newBalance = rawBalance - paymentAmount;
        if (newBalance < 0) newBalance = 0; // Prevent negative display if they overpay

        const displayNewBalance = document.getElementById('display_new_balance');
        if (displayNewBalance) {
            displayNewBalance.value = 'Rp ' + formatRibuan(newBalance);
        }
    }

    const paymentModal = document.getElementById('paymentModal');
    if (paymentModal) {
        paymentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const purchaseId = button.getAttribute('data-id');
            const invoice = button.getAttribute('data-invoice');
            const balance = button.getAttribute('data-balance');
            const dueDate = button.getAttribute('data-duedate');

            const form = document.getElementById('paymentForm');
            form.action = `/purchases/${purchaseId}/payments`;

            document.getElementById('paymentInvoice').textContent = invoice;
            document.getElementById('display_remaining_balance').value = 'Rp ' + formatRibuan(balance);
            document.getElementById('raw_remaining_balance').value = balance;
            document.getElementById('payment_amount').value = ''; // Biarkan kosong agar diinput sendiri
            
            calculateNewBalance();

            const dueDateInput = document.getElementById('due_date');
            if (dueDateInput) {
                dueDateInput.value = dueDate;
            }
        });
    }
});
</script>
@endpush
