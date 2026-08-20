@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('page-subtitle', 'Ubah data produk')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="code" class="form-label">Kode Produk (SKU) <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $product->code) }}" placeholder="Contoh: PRD-001" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $product->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                            <option value="Lainnya_Baru">+ Tambah Kategori Baru...</option>
                        </select>
                        <input type="text" name="category_new" id="category_new" class="form-control mt-2 d-none" placeholder="Ketik kategori baru..." value="{{ old('category_new') }}" autocomplete="off">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="selling_price" class="form-label">Harga Jual <span class="text-danger">*</span> <small class="text-muted">(Tidak dapat diubah)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="selling_price" id="selling_price" class="form-control" value="{{ old('selling_price', intval($product->selling_price)) }}" readonly>
                        </div>
                        @error('selling_price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="unit" class="form-label">Satuan</label>
                        <select name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror">
                            <option value="">Pilih Satuan (Opsional)</option>
                            @foreach($units as $un)
                                <option value="{{ $un }}" {{ old('unit', $product->unit) == $un ? 'selected' : '' }}>{{ $un }}</option>
                            @endforeach
                            <option value="Lainnya_Baru">+ Tambah Satuan Baru...</option>
                        </select>
                        <input type="text" name="unit_new" id="unit_new" class="form-control mt-2 d-none" placeholder="Ketik satuan baru..." value="{{ old('unit_new') }}" autocomplete="off">
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="min_stock" class="form-label">Stok Minimal</label>
                        <input type="number" name="min_stock" id="min_stock" class="form-control @error('min_stock') is-invalid @enderror" value="{{ old('min_stock', $product->min_stock ?? 0) }}">
                        @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar Produk</label>
                        @if($product->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="max-height: 100px;">
                            </div>
                        @endif
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktifkan Produk</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatting nominal selling_price
    const priceInput = document.getElementById('selling_price');
    if (priceInput) {
        priceInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            this.value = rupiah;
        });
        // trigger event on load if has value
        if(priceInput.value) {
            priceInput.dispatchEvent(new Event('input'));
        }
    }

    // Category Logic
    const categorySelect = document.getElementById('category');
    const categoryNew = document.getElementById('category_new');

    // Default value injection for edit (if category is not in list but exists in DB)
    let hasCategory = false;
    Array.from(categorySelect.options).forEach(opt => {
        if (opt.value == "{{ old('category', $product->category) }}") hasCategory = true;
    });

    if (!hasCategory && "{{ old('category', $product->category) }}" !== "") {
        categorySelect.value = 'Lainnya_Baru';
        categoryNew.value = "{{ old('category', $product->category) }}";
    }

    if(categorySelect) {
        categorySelect.addEventListener('change', function() {
            if (this.value === 'Lainnya_Baru') {
                categoryNew.classList.remove('d-none');
                categoryNew.required = true;
                categoryNew.name = 'category';
                categorySelect.name = 'category_select';
            } else {
                categoryNew.classList.add('d-none');
                categoryNew.required = false;
                categoryNew.name = 'category_new';
                categorySelect.name = 'category';
            }
        });
        if(categorySelect.value === 'Lainnya_Baru' || (categoryNew && categoryNew.value)) {
            categorySelect.value = 'Lainnya_Baru';
            categorySelect.dispatchEvent(new Event('change'));
        }
    }

    // Unit Logic
    const unitSelect = document.getElementById('unit');
    const unitNew = document.getElementById('unit_new');

    let hasUnit = false;
    Array.from(unitSelect.options).forEach(opt => {
        if (opt.value == "{{ old('unit', $product->unit) }}") hasUnit = true;
    });

    if (!hasUnit && "{{ old('unit', $product->unit) }}" !== "") {
        unitSelect.value = 'Lainnya_Baru';
        unitNew.value = "{{ old('unit', $product->unit) }}";
    }

    if(unitSelect) {
        unitSelect.addEventListener('change', function() {
            if (this.value === 'Lainnya_Baru') {
                unitNew.classList.remove('d-none');
                unitNew.required = true;
                unitNew.name = 'unit';
                unitSelect.name = 'unit_select';
            } else {
                unitNew.classList.add('d-none');
                unitNew.required = false;
                unitNew.name = 'unit_new';
                unitSelect.name = 'unit';
            }
        });
        if(unitSelect.value === 'Lainnya_Baru' || (unitNew && unitNew.value)) {
            unitSelect.value = 'Lainnya_Baru';
            unitSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
