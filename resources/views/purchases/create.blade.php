@extends('layouts.app')

@section('title', 'Tambah Pembelian')
@section('page-title', 'Tambah Pembelian Baru')
@section('page-subtitle', 'Masukkan data pembelian barang masuk')

@section('content')
<style>
    .item-row {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        border: 1px solid #dee2e6;
    }
    .item-row .remove-item {
        margin-top: 32px;
    }
    .profit-info {
        font-size: 0.85rem;
        margin-top: 5px;
    }
</style>

<div class="card">
    <div class="card-body">
        @if($errors->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Peringatan!</strong><br>
                {{ $errors->first('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data">
            @csrf

            <!-- Header Purchase -->
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                            <option value="NEW">+ Tambah Supplier Baru...</option>
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="invoice_number" class="form-label">No. Faktur <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control bg-light @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', $invoiceNumber) }}" readonly required>
                        @error('invoice_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="purchase_date" class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-list"></i> <strong>Daftar Barang</strong>
                </div>
                <div class="card-body">
                    <div id="items-container">
                        <div class="item-row">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <label class="form-label">Produk <span class="text-danger">*</span></label>
                                        <select name="items[0][product_id]" class="form-control product-select" required>
                                            <option value="">Pilih Produk</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-purchase="{{ $product->purchase_price }}" data-selling="{{ $product->selling_price }}">
                                                {{ $product->name }} ({{ $product->code ?? 'Tidak ada kode' }})
                                            </option>
                                            @endforeach
                                            <option value="NEW">+ Tambah Produk Baru...</option>
                                        </select>
                                        <div class="profit-info text-muted d-none">
                                            Harga Jual: <span class="selling-price-text">0</span> | Laba: <span class="profit-text font-weight-bold">0</span>/pcs
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-2">
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity" value="1" min="1" required autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label class="form-label">Harga Beli / pcs <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="items[0][purchase_price]" class="form-control purchase-price" required autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <label class="form-label">Subtotal</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control subtotal" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="mb-2">
                                        <label class="form-label">Catatan Item</label>
                                        <input type="text" name="items[0][notes]" class="form-control" placeholder="Catatan untuk item ini" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success" onclick="addItem()">
                        <i class="bi bi-plus-circle"></i> Tambah Barang
                    </button>
                </div>
            </div>

            <!-- Footer Purchase -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tax" class="form-label">Pajak (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="tax" id="tax" class="form-control nominal-input" value="{{ old('tax', 0) }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shipping_cost" class="form-label">Ongkos Kirim (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="shipping_cost" id="shipping_cost" class="form-control nominal-input" value="{{ old('shipping_cost', 0) }}" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 offset-md-4">
                    <div class="mb-3">
                        <label for="grand_total" class="form-label">Grand Total</label>
                        <input type="text" id="grand_total" class="form-control bg-light text-end text-primary" readonly style="font-weight: bold; font-size: 1.5rem;" value="Rp 0">
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_status" id="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                            <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Belum Dibayar</option>
                            <option value="partial" {{ old('payment_status') == 'partial' ? 'selected' : '' }}>Dibayar Sebagian</option>
                            <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4 d-none" id="paid_amount_container">
                    <div class="mb-3">
                        <label for="paid_amount" class="form-label">Jumlah Dibayar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="paid_amount" id="paid_amount" class="form-control nominal-input @error('paid_amount') is-invalid @enderror" value="{{ old('paid_amount', 0) }}" autocomplete="off">
                        </div>
                        @error('paid_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                            <option value="">Pilih Metode</option>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm }}" {{ old('payment_method') == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                            @endforeach
                            <option value="Lainnya_Baru">+ Tambah Metode Baru...</option>
                        </select>
                        <input type="text" name="payment_method_new" id="payment_method_new" class="form-control mt-2 d-none" placeholder="Ketik metode baru..." value="{{ old('payment_method_new') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4" id="due_date_container">
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="receipt_image" class="form-label">Upload Nota Transaksi (Opsional)</label>
                        <input type="file" name="receipt_image" id="receipt_image" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG. Maksimal 5MB.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-lg">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-save"></i> Simpan Pembelian
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('modals')
<!-- Modal Tambah Supplier -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Supplier Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
            <input type="text" id="ajax_supplier_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveSupplier">Simpan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Produk Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
            <input type="text" id="ajax_product_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kode Produk (SKU)</label>
            <input type="text" id="ajax_product_code" class="form-control" placeholder="Kosongkan untuk auto-generate">
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori <span class="text-danger">*</span></label>
            <select id="ajax_product_category" class="form-control" required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
                <option value="Lainnya_Baru">+ Tambah Kategori Baru...</option>
            </select>
            <input type="text" id="ajax_product_category_new" class="form-control mt-2 d-none" placeholder="Ketik kategori baru..." autocomplete="off">
        </div>
        <div class="mb-3">
            <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" id="ajax_product_selling_price" class="form-control nominal-input" required>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveProduct">Simpan</button>
      </div>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
    let itemCount = 1;
    let activeProductSelect = null;

    // Helper: format nominal
    function formatRibuan(angka) {
        let number_string = angka.toString().replace(/[^,\d]/g, '');
        let split = number_string.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function parseRibuan(str) {
        if(!str) return 0;
        return parseFloat(str.toString().replace(/\./g, '')) || 0;
    }

    function initNominalInputs() {
        document.querySelectorAll('.nominal-input, .purchase-price, .subtotal').forEach(input => {
            input.removeEventListener('input', handleNominalInput);
            input.addEventListener('input', handleNominalInput);
        });
    }

    function handleNominalInput(e) {
        let val = e.target.value;
        e.target.value = formatRibuan(val);
    }

    function addItem() {
        const container = document.getElementById('items-container');
        const newItem = document.createElement('div');
        newItem.className = 'item-row';
        newItem.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-2">
                        <label class="form-label">Produk <span class="text-danger">*</span></label>
                        <select name="items[${itemCount}][product_id]" class="form-control product-select" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-purchase="{{ $product->purchase_price }}" data-selling="{{ $product->selling_price }}">
                                {{ $product->name }} ({{ $product->code ?? 'Tidak ada kode' }})
                            </option>
                            @endforeach
                            <option value="NEW">+ Tambah Produk Baru...</option>
                        </select>
                        <div class="profit-info text-muted d-none">
                            Harga Jual: <span class="selling-price-text">0</span> | Laba: <span class="profit-text font-weight-bold">0</span>/pcs
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemCount}][quantity]" class="form-control quantity" value="1" min="1" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-2">
                        <label class="form-label">Harga Beli / pcs <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="items[${itemCount}][purchase_price]" class="form-control purchase-price" required autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-2">
                        <label class="form-label">Subtotal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control subtotal" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-10">
                    <div class="mb-2">
                        <label class="form-label">Catatan Item</label>
                        <input type="text" name="items[${itemCount}][notes]" class="form-control" placeholder="Catatan untuk item ini" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-item w-100" onclick="removeItem(this)">Hapus</button>
                </div>
            </div>
        `;
        container.appendChild(newItem);
        itemCount++;
        initNominalInputs();
        updateGrandTotal();
    }

    function removeItem(btn) {
        if (document.querySelectorAll('.item-row').length > 1) {
            btn.closest('.item-row').remove();
            updateGrandTotal();
        } else {
            alert('Minimal harus ada 1 item!');
        }
    }

    // Bidirectional Calculation & Profit Logic
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity')) {
            const row = e.target.closest('.item-row');
            const qty = parseFloat(e.target.value) || 0;
            const price = parseRibuan(row.querySelector('.purchase-price').value);
            row.querySelector('.subtotal').value = formatRibuan(qty * price);
            updateGrandTotal();
        }
        else if (e.target.classList.contains('purchase-price')) {
            const row = e.target.closest('.item-row');
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseRibuan(e.target.value);
            row.querySelector('.subtotal').value = formatRibuan(qty * price);

            // update profit
            updateProfit(row, price);
            updateGrandTotal();
        }
        else if (e.target.classList.contains('subtotal')) {
            const row = e.target.closest('.item-row');
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const subtotal = parseRibuan(e.target.value);
            if (qty > 0) {
                const price = Math.round(subtotal / qty);
                row.querySelector('.purchase-price').value = formatRibuan(price);
                updateProfit(row, price);
            }
            updateGrandTotal();
        }
        else if (e.target.id === 'tax' || e.target.id === 'shipping_cost') {
            updateGrandTotal();
        }
    });

    function updateProfit(row, purchasePrice) {
        const select = row.querySelector('.product-select');
        if(select && select.selectedOptions.length > 0 && select.value !== '' && select.value !== 'NEW') {
            const sellingPrice = parseFloat(select.selectedOptions[0].dataset.selling) || 0;
            const profit = sellingPrice - purchasePrice;

            const infoDiv = row.querySelector('.profit-info');
            infoDiv.classList.remove('d-none');
            row.querySelector('.selling-price-text').innerText = 'Rp ' + formatRibuan(sellingPrice);

            const profitSpan = row.querySelector('.profit-text');
            profitSpan.innerText = 'Rp ' + formatRibuan(profit);

            if(profit <= 0) {
                profitSpan.classList.remove('text-success');
                profitSpan.classList.add('text-danger');
                profitSpan.innerHTML += ' <i class="bi bi-exclamation-circle"></i> (Peringatan: Laba 0 atau Rugi!)';
            } else {
                profitSpan.classList.remove('text-danger');
                profitSpan.classList.add('text-success');
            }
        } else {
            row.querySelector('.profit-info').classList.add('d-none');
        }
    }

    function updateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(input => {
            total += parseRibuan(input.value);
        });

        const tax = parseRibuan(document.getElementById('tax').value);
        const shipping = parseRibuan(document.getElementById('shipping_cost').value);

        const grandTotal = total + tax + shipping;
        document.getElementById('grand_total').value = 'Rp ' + formatRibuan(grandTotal);
    }

    // Payment Method Logic
    const pmSelect = document.getElementById('payment_method');
    const pmNew = document.getElementById('payment_method_new');
    if(pmSelect) {
        pmSelect.addEventListener('change', function() {
            if (this.value === 'Lainnya_Baru') {
                pmNew.classList.remove('d-none');
                pmNew.required = true;
            } else {
                pmNew.classList.add('d-none');
                pmNew.required = false;
            }
        });
    }

    // Payment Status Logic
    const paymentStatusSelect = document.getElementById('payment_status');
    const dueDateContainer = document.getElementById('due_date_container');
    const dueDateInput = document.getElementById('due_date');
    const paidAmountContainer = document.getElementById('paid_amount_container');
    const paidAmountInput = document.getElementById('paid_amount');

    function togglePaymentStatus() {
        if(paymentStatusSelect.value === 'paid') {
            dueDateContainer.classList.add('d-none');
            dueDateInput.value = '';
            paidAmountContainer.classList.add('d-none');
            paidAmountInput.value = '';
        } else if(paymentStatusSelect.value === 'partial') {
            dueDateContainer.classList.remove('d-none');
            paidAmountContainer.classList.remove('d-none');
        } else {
            dueDateContainer.classList.remove('d-none');
            paidAmountContainer.classList.add('d-none');
            paidAmountInput.value = '';
        }
    }

    if (paymentStatusSelect) {
        paymentStatusSelect.addEventListener('change', togglePaymentStatus);
        togglePaymentStatus();
    }

    let supplierModal;
    let productModal;

    document.addEventListener('DOMContentLoaded', function() {
        // Modals Logic
        const smEl = document.getElementById('supplierModal');
        if(smEl) supplierModal = new bootstrap.Modal(smEl);

        const pmEl = document.getElementById('productModal');
        if(pmEl) productModal = new bootstrap.Modal(pmEl);
    });

    const supplierSelect = document.getElementById('supplier_id');
    if(supplierSelect) {
        supplierSelect.addEventListener('change', function() {
            if(this.value === 'NEW') {
                document.getElementById('ajax_supplier_name').value = '';
                if(supplierModal) supplierModal.show();
                this.value = ''; // reset temporary
            }
        });
    }

    document.getElementById('btnSaveSupplier').addEventListener('click', function() {
        const name = document.getElementById('ajax_supplier_name').value;
        if(!name) { alert('Nama supplier wajib diisi!'); return; }

        this.disabled = true;
        this.innerText = 'Menyimpan...';

        fetch('{{ route("purchases.ajax.supplier") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const opt = new Option(data.supplier.name, data.supplier.id, true, true);
                // Insert before the last option ("+ Tambah Supplier Baru...")
                supplierSelect.add(opt, supplierSelect.options[supplierSelect.options.length - 1]);
                supplierSelect.value = data.supplier.id;
                if(supplierModal) supplierModal.hide();
            } else {
                alert('Gagal menyimpan supplier');
            }
        })
        .catch(err => alert('Error: ' + err))
        .finally(() => {
            this.disabled = false;
            this.innerText = 'Simpan';
        });
    });

    // Product Modal Logic
    document.addEventListener('change', function(e) {
        if(e.target.classList.contains('product-select')) {
            if(e.target.value === 'NEW') {
                activeProductSelect = e.target;
                document.getElementById('ajax_product_name').value = '';
                document.getElementById('ajax_product_code').value = '';
                document.getElementById('ajax_product_category').value = '';
                document.getElementById('ajax_product_category_new').value = '';
                document.getElementById('ajax_product_category_new').classList.add('d-none');
                document.getElementById('ajax_product_selling_price').value = '';
                if(productModal) productModal.show();
                e.target.value = ''; // reset
            } else {
                // Update profit info immediately if changed to existing product
                const row = e.target.closest('.item-row');
                const pPrice = parseRibuan(row.querySelector('.purchase-price').value);
                updateProfit(row, pPrice);
            }
        }
    });

    // Category in Modal Logic
    const ajaxCategorySelect = document.getElementById('ajax_product_category');
    const ajaxCategoryNew = document.getElementById('ajax_product_category_new');
    if(ajaxCategorySelect) {
        ajaxCategorySelect.addEventListener('change', function() {
            if (this.value === 'Lainnya_Baru') {
                ajaxCategoryNew.classList.remove('d-none');
                ajaxCategoryNew.required = true;
            } else {
                ajaxCategoryNew.classList.add('d-none');
                ajaxCategoryNew.required = false;
            }
        });
    }

    document.getElementById('btnSaveProduct').addEventListener('click', function() {
        const name = document.getElementById('ajax_product_name').value;
        const code = document.getElementById('ajax_product_code').value;
        let cat = document.getElementById('ajax_product_category').value;
        if(cat === 'Lainnya_Baru') {
            cat = document.getElementById('ajax_product_category_new').value;
        }
        const price = document.getElementById('ajax_product_selling_price').value;

        if(!name || !cat || !price) { alert('Harap isi semua field wajib!'); return; }

        this.disabled = true;
        this.innerText = 'Menyimpan...';

        fetch('{{ route("purchases.ajax.product") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name, code: code, category: cat, selling_price: price })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Add to ALL product selects
                document.querySelectorAll('.product-select').forEach(select => {
                    const opt = document.createElement('option');
                    opt.value = data.product.id;
                    opt.text = data.product.code + ' - ' + data.product.name;
                    opt.dataset.purchase = 0;
                    opt.dataset.selling = data.product.selling_price;

                    // Insert before NEW option
                    select.add(opt, select.options[select.options.length - 1]);
                });

                if(activeProductSelect) {
                    activeProductSelect.value = data.product.id;

                    // Trigger change to update price immediately
                    const row = activeProductSelect.closest('.item-row');
                    const pPrice = parseRibuan(row.querySelector('.purchase-price').value);
                    updateProfit(row, pPrice);
                }
                if(productModal) productModal.hide();
            } else {
                alert('Gagal menyimpan produk');
            }
        })
        .catch(err => alert('Error: ' + err))
        .finally(() => {
            this.disabled = false;
            this.innerText = 'Simpan';
        });
    });

    // Initialize
    initNominalInputs();
</script>
@endpush
