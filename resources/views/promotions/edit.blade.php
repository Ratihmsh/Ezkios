@extends('layouts.app')

@section('title', 'Edit Promosi')
@section('page-title', 'Edit Promosi & Diskon')
@section('page-subtitle', 'Ubah aturan diskon, kupon, atau kenaikan harga')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('promotions.update', $promotion->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- BASIC INFO -->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktifkan Promosi Ini</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Promosi <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $promotion->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">Tipe Promosi <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="product_discount" {{ old('type', $promotion->type) == 'product_discount' ? 'selected' : '' }}>Diskon Produk Spesifik</option>
                        <option value="product_markup" {{ old('type', $promotion->type) == 'product_markup' ? 'selected' : '' }}>Kenaikan Harga (Markup) Produk Spesifik</option>
                        <option value="category_discount" {{ old('type', $promotion->type) == 'category_discount' ? 'selected' : '' }}>Diskon per Kategori Produk</option>
                        <option value="transaction_discount" {{ old('type', $promotion->type) == 'transaction_discount' ? 'selected' : '' }}>Diskon Global (Transaksi)</option>
                        <option value="buy_x_get_y" {{ old('type', $promotion->type) == 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y (Beli 1 Gratis 1, dsb)</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="text-muted">

            <!-- TARGETS -->
            <div class="row">
                <div class="col-md-6 mb-3" id="product_container">
                    <label for="product_id" class="form-label">Pilih Produk Target <span class="text-danger">*</span></label>
                    <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id', $promotion->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Rp {{ number_format($product->selling_price, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3" id="category_container" style="display: none;">
                    <label for="category_name" class="form-label">Pilih Kategori Target <span class="text-danger">*</span></label>
                    <select name="category_name" id="category_name" class="form-select @error('category_name') is-invalid @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ old('category_name', $promotion->category_name) == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3" id="reward_product_container" style="display: none;">
                    <label for="reward_product_id" class="form-label">Produk Hadiah / Gratis (Get Y) <span class="text-danger">*</span></label>
                    <select name="reward_product_id" id="reward_product_id" class="form-select @error('reward_product_id') is-invalid @enderror">
                        <option value="">-- Pilih Produk Reward --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('reward_product_id', $promotion->reward_product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Stok: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                    @error('reward_product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- CONDITIONS -->
            <div class="row">
                <div class="col-md-4 mb-3" id="min_qty_container">
                    <label for="min_qty" class="form-label">Syarat Minimal Qty (Buy X) <span class="text-danger">*</span></label>
                    <input type="number" name="min_qty" id="min_qty" class="form-control @error('min_qty') is-invalid @enderror" value="{{ old('min_qty', $promotion->min_qty) }}" required min="1">
                    @error('min_qty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3" id="reward_qty_container" style="display: none;">
                    <label for="reward_qty" class="form-label">Jumlah Hadiah (Get Y) <span class="text-danger">*</span></label>
                    <input type="number" name="reward_qty" id="reward_qty" class="form-control @error('reward_qty') is-invalid @enderror" value="{{ old('reward_qty', $promotion->reward_qty ?? 1) }}" min="1">
                    @error('reward_qty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3" id="min_spend_container" style="display: none;">
                    <label for="min_spend" class="form-label">Syarat Minimal Belanja <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="min_spend" id="min_spend" class="form-control @error('min_spend') is-invalid @enderror" value="{{ old('min_spend', $promotion->min_spend) }}" required min="0">
                    </div>
                    @error('min_spend')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- VALUES -->
            <div class="row" id="value_container">
                <div class="col-md-6 mb-3">
                    <label for="value_type" class="form-label">Tipe Nilai Promo <span class="text-danger">*</span></label>
                    <select name="value_type" id="value_type" class="form-select @error('value_type') is-invalid @enderror" required>
                        <option value="percentage" {{ old('value_type', $promotion->value_type) == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="fixed_amount" {{ old('value_type', $promotion->value_type) == 'fixed_amount' ? 'selected' : '' }}>Nominal (Rupiah)</option>
                    </select>
                    @error('value_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="value" class="form-label">Nilai Potongan/Kenaikan <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="value" id="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $promotion->value) }}" required>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="text-muted">

            <!-- ADVANCED CONDITIONS -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="promo_code" class="form-label">Kode Voucher / Kupon (Opsional)</label>
                    <input type="text" name="promo_code" id="promo_code" class="form-control @error('promo_code') is-invalid @enderror" value="{{ old('promo_code', $promotion->promo_code) }}">
                    @error('promo_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="usage_limit" class="form-label">Kuota Promo (Opsional)</label>
                    <input type="number" name="usage_limit" id="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', $promotion->usage_limit) }}" min="1">
                    @error('usage_limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="payment_method" class="form-label">Metode Pembayaran (Opsional)</label>
                    <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                        <option value="">-- Semua Metode --</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm }}" {{ old('payment_method', $promotion->payment_method) == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                        @endforeach
                        <option value="Lainnya_Baru">+ Tambah Baru...</option>
                    </select>
                    <input type="text" name="payment_method_new" id="payment_method_new" class="form-control mt-2 d-none" placeholder="Masukkan Metode Baru">
                    <small class="text-muted">Promo hanya berlaku untuk metode ini.</small>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label">Tanggal Mulai Berlaku (Opsional)</label>
                    <input type="datetime-local" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $promotion->start_date ? $promotion->start_date->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label">Tanggal Berakhir Berlaku (Opsional)</label>
                    <input type="datetime-local" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $promotion->end_date ? $promotion->end_date->format('Y-m-d\TH:i') : '') }}">
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('promotions.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');

        const productContainer = document.getElementById('product_container');
        const categoryContainer = document.getElementById('category_container');
        const rewardProductContainer = document.getElementById('reward_product_container');

        const minQtyContainer = document.getElementById('min_qty_container');
        const rewardQtyContainer = document.getElementById('reward_qty_container');
        const minSpendContainer = document.getElementById('min_spend_container');
        const valueContainer = document.getElementById('value_container');

        function toggleFields() {
            const type = typeSelect.value;

            // Reset visibility
            productContainer.style.display = 'none';
            categoryContainer.style.display = 'none';
            rewardProductContainer.style.display = 'none';
            minQtyContainer.style.display = 'none';
            rewardQtyContainer.style.display = 'none';
            minSpendContainer.style.display = 'none';
            valueContainer.style.display = 'none';

            // Reset requirements
            document.getElementById('product_id').required = false;
            document.getElementById('category_name').required = false;
            document.getElementById('reward_product_id').required = false;
            document.getElementById('value').required = false;
            if(!document.getElementById('min_spend').value) document.getElementById('min_spend').value = 0;

            if (type === 'transaction_discount') {
                minSpendContainer.style.display = 'block';
                valueContainer.style.display = 'flex';
                document.getElementById('value').required = true;
            }
            else if (type === 'category_discount') {
                categoryContainer.style.display = 'block';
                document.getElementById('category_name').required = true;
                minQtyContainer.style.display = 'block';
                valueContainer.style.display = 'flex';
                document.getElementById('value').required = true;
            }
            else if (type === 'buy_x_get_y') {
                productContainer.style.display = 'block';
                document.getElementById('product_id').required = true;
                minQtyContainer.style.display = 'block';
                rewardProductContainer.style.display = 'block';
                document.getElementById('reward_product_id').required = true;
                rewardQtyContainer.style.display = 'block';

                // Set value 0 on save
                document.getElementById('value').value = 0;
            }
            else {
                // product_discount & product_markup
                productContainer.style.display = 'block';
                document.getElementById('product_id').required = true;
                minQtyContainer.style.display = 'block';
                valueContainer.style.display = 'flex';
                document.getElementById('value').required = true;
            }
        }

        typeSelect.addEventListener('change', toggleFields);
        toggleFields(); // Initial call

        const paymentMethodSelect = document.getElementById('payment_method');
        const paymentMethodNew = document.getElementById('payment_method_new');
        if(paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'Lainnya_Baru') {
                    paymentMethodNew.classList.remove('d-none');
                    paymentMethodNew.required = true;
                } else {
                    paymentMethodNew.classList.add('d-none');
                    paymentMethodNew.required = false;
                    paymentMethodNew.value = '';
                }
            });
            // trigger on load
            if (paymentMethodSelect.value === 'Lainnya_Baru') {
                paymentMethodNew.classList.remove('d-none');
                paymentMethodNew.required = true;
            }
        }
    });
</script>
@endpush
