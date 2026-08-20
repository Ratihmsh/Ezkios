@extends('layouts.app')

@section('title', 'Edit Penjualan (POS)')
@section('page-title', 'Edit Penjualan')
@section('page-subtitle', 'Ubah data transaksi penjualan')

@section('content')
<style>
    .pos-layout {
        display: flex;
        gap: 20px;
        height: calc(100vh - 180px);
        min-height: 600px;
    }
    .pos-catalog {
        flex: 1 1 65%;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #dee2e6;
        overflow: hidden;
    }
    .pos-cart {
        flex: 1 1 35%;
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
    }
    .category-pills {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .category-pills::-webkit-scrollbar {
        height: 6px;
    }
    .category-pills::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }
    .product-grid {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }
    .product-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e9ecef;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }
    .product-img {
        height: 120px;
        object-fit: contain;
        background: #fff;
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    .cart-items {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
        padding-right: 5px;
    }
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed #e9ecef;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .cart-item-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 2px;
    }
    .cart-item-price {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .qty-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .qty-input {
        width: 45px;
        text-align: center;
        height: 28px;
        padding: 0;
        font-size: 0.9rem;
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
        font-size: 0.95rem;
    }
    .grand-total {
        font-size: 1.5rem;
        font-weight: bold;
        color: #198754;
    }
</style>

<div class="pos-layout">
    <!-- Left: Catalog -->
    <div class="pos-catalog">
        <div class="mb-3">
            <input type="text" id="searchProduct" class="form-control form-control-lg" placeholder="Cari berdasarkan nama atau kode produk..." autocomplete="off">
        </div>

        <div class="category-pills" id="categoryPills">
            <button class="btn btn-primary rounded-pill cat-btn" data-category="all">Semua</button>
            @foreach($categories as $cat)
                <button class="btn btn-outline-primary rounded-pill cat-btn" data-category="{{ $cat }}">{{ $cat }}</button>
            @endforeach
        </div>

        <div class="product-grid">
            <div class="row g-3" id="productGridList">
                @foreach($products as $product)
                <div class="col-md-4 col-lg-3 col-6 product-item" data-name="{{ strtolower($product->name) }}" data-code="{{ strtolower($product->code) }}" data-category="{{ $product->category }}">
                    <div class="card product-card" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->code }}', {{ $product->selling_price }}, {{ $product->stock }})">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" class="card-img-top product-img" alt="{{ $product->name }}">
                        @else
                            <div class="card-img-top product-img d-flex align-items-center justify-content-center bg-light text-muted">
                                <i class="bi bi-box" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                            <p class="card-text text-success fw-bold mb-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                            <small class="text-muted d-block">Stok: {{ $product->stock }}</small>
                            <small class="text-muted">{{ $product->code }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
                <div id="noProductMsg" class="col-12 text-center text-muted d-none py-5">
                    <i class="bi bi-search fs-1"></i>
                    <p class="mt-2">Produk tidak ditemukan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Cart -->
    <div class="pos-cart">
        <form action="{{ route('sales.update', $sale) }}" method="POST" id="posForm" class="d-flex flex-column h-100">
            @csrf
            @method('PUT')
            <h5 class="border-bottom pb-2"><i class="bi bi-cart3"></i> Keranjang Belanja</h5>

            <div class="cart-items" id="cartContainer">
                <div class="text-center text-muted py-5" id="emptyCartMsg">
                    Keranjang masih kosong
                </div>
                <!-- Cart items will be injected here -->
            </div>

            <!-- Transaction Details -->
            <div class="checkout-details bg-light p-3 rounded mb-3">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label mb-0 small">Pelanggan</label>
                        <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ $sale->customer_name }}" placeholder="Nama Umum">
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-0 small">No Faktur</label>
                        <input type="text" name="invoice_number" class="form-control form-control-sm" value="{{ $sale->invoice_number }}" readonly>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label mb-0 small">Tgl Jual</label>
                        <input type="date" name="sale_date" class="form-control form-control-sm" value="{{ $sale->sale_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-0 small">Catatan</label>
                        <input type="text" name="notes" class="form-control form-control-sm" value="{{ $sale->notes }}" placeholder="Opsional">
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span id="txtSubtotal">Rp 0</span>
                </div>
                <div class="totals-row">
                    <span>Diskon (Rp)</span>
                    <input type="text" id="inputDiscount" name="discount" class="form-control form-control-sm w-25 text-end nominal-input" value="{{ number_format($sale->discount, 0, '', '') }}">
                </div>
                <div class="totals-row">
                    <span>Pajak (Rp)</span>
                    <input type="text" id="inputTax" name="tax" class="form-control form-control-sm w-25 text-end nominal-input" value="{{ number_format($sale->tax, 0, '', '') }}">
                </div>
                <div class="totals-row font-weight-bold mt-2 pt-2 border-top">
                    <span class="fs-5">TOTAL</span>
                    <span class="grand-total" id="txtTotal">Rp 0</span>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="payment-section mt-3">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label mb-0 small">Status <span class="text-danger">*</span></label>
                        <select name="payment_status" id="payment_status" class="form-select form-select-sm" required>
                            <option value="paid" {{ $sale->payment_status == 'paid' ? 'selected' : '' }}>Lunas</option>
                            <option value="partial" {{ $sale->payment_status == 'partial' ? 'selected' : '' }}>Bayar Sebagian</option>
                            <option value="pending" {{ $sale->payment_status == 'pending' ? 'selected' : '' }}>Belum Dibayar</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-0 small">Metode <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select form-select-sm" required>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm }}" {{ $sale->payment_method == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                            @endforeach
                            <option value="Lainnya_Baru">+ Tambah Baru...</option>
                        </select>
                        <input type="text" name="payment_method_new" id="payment_method_new" class="form-control form-control-sm mt-1 d-none" placeholder="Metode Baru">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12" id="dueDateContainer">
                        <label class="form-label mb-0 small">Jatuh Tempo</label>
                        <input type="date" name="due_date" id="due_date" class="form-control form-control-sm" value="{{ $sale->due_date ? \Carbon\Carbon::parse($sale->due_date)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-6" id="paidAmountContainer">
                        <label class="form-label mb-0 small">Uang Diterima</label>
                        <input type="text" name="paid_amount" id="inputPaid" class="form-control form-control-sm text-end nominal-input" value="{{ number_format($sale->paid_amount, 0, '', '') }}">
                    </div>
                    <div class="col-6" id="changeAmountContainer">
                        <label class="form-label mb-0 small">Kembalian</label>
                        <input type="text" id="txtChange" class="form-control form-control-sm text-end bg-light" readonly value="Rp 0">
                    </div>
                </div>
            </div>

            <!-- Container for dynamic hidden inputs for cart items -->
            <div id="hiddenInputsContainer"></div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary w-50">Batal</a>
                <button type="submit" class="btn btn-success w-50" id="btnSubmit">
                    <i class="bi bi-save"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Cart State
    let cart = {};

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID').format(number);
    };

    const parseRibuan = (str) => {
        if (!str) return 0;
        return parseInt(str.toString().replace(/[^0-9]/g, ''), 10) || 0;
    };

    // Prepopulate Cart from Sale Items
    @foreach($sale->items as $item)
        @php
            $product = $item->product;
            // Add back the quantity to stock for correct max stock logic during edit
            $stock = $product->stock + $item->quantity;
        @endphp
        cart[{{ $product->id }}] = {
            id: {{ $product->id }},
            name: '{{ addslashes($product->name) }}',
            code: '{{ $product->code }}',
            price: {{ $item->selling_price }},
            qty: {{ $item->quantity }},
            stock: {{ $stock }}
        };
    @endforeach

    // Filter Logic
    const searchInput = document.getElementById('searchProduct');
    const catButtons = document.querySelectorAll('.cat-btn');
    const productItems = document.querySelectorAll('.product-item');
    const noProductMsg = document.getElementById('noProductMsg');

    let currentCategory = 'all';

    function filterProducts() {
        const query = searchInput.value.toLowerCase();
        let visibleCount = 0;

        productItems.forEach(item => {
            const name = item.getAttribute('data-name');
            const code = item.getAttribute('data-code');
            const cat = item.getAttribute('data-category') || '';

            const matchQuery = name.includes(query) || code.includes(query);
            const matchCat = currentCategory === 'all' || cat === currentCategory;

            if (matchQuery && matchCat) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noProductMsg.classList.remove('d-none');
        } else {
            noProductMsg.classList.add('d-none');
        }
    }

    searchInput.addEventListener('input', filterProducts);

    catButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            catButtons.forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');

            currentCategory = this.getAttribute('data-category');
            filterProducts();
        });
    });

    // Cart Logic
    function addToCart(id, name, code, price, stock) {
        if (stock <= 0 && !cart[id]) {
            alert('Stok produk habis!');
            return;
        }

        if (cart[id]) {
            if (cart[id].qty < cart[id].stock) {
                cart[id].qty++;
            } else {
                alert('Stok maksimal tercapai!');
            }
        } else {
            cart[id] = { id, name, code, price, qty: 1, stock };
        }
        renderCart();
    }

    // Expose functions to global scope
    window.addToCart = addToCart;
    window.updateQty = updateQty;
    window.manualUpdateQty = manualUpdateQty;
    window.removeCartItem = removeCartItem;

    function updateQty(id, change) {
        if (!cart[id]) return;

        const newQty = cart[id].qty + change;
        if (newQty <= 0) {
            delete cart[id];
        } else if (newQty > cart[id].stock) {
            alert('Stok maksimal tercapai!');
        } else {
            cart[id].qty = newQty;
        }
        renderCart();
    }

    function manualUpdateQty(id, value) {
        if (!cart[id]) return;
        let newQty = parseInt(value) || 1;
        if (newQty <= 0) newQty = 1;
        if (newQty > cart[id].stock) {
            alert('Stok tidak mencukupi! Maksimal: ' + cart[id].stock);
            newQty = cart[id].stock;
        }
        cart[id].qty = newQty;
        renderCart();
    }

    function removeCartItem(id) {
        delete cart[id];
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        const emptyMsg = document.getElementById('emptyCartMsg');
        const hiddenInputs = document.getElementById('hiddenInputsContainer');
        const btnSubmit = document.getElementById('btnSubmit');

        hiddenInputs.innerHTML = ''; // Clear old inputs

        let subtotal = 0;
        let html = '';
        let index = 0;

        for (const id in cart) {
            const item = cart[id];
            const itemTotal = item.price * item.qty;
            subtotal += itemTotal;

            html += `
                <div class="cart-item">
                    <div style="flex: 1; min-width: 0;">
                        <div class="cart-item-title text-truncate" title="${item.name}">${item.name}</div>
                        <div class="cart-item-price text-success">Rp ${formatRupiah(item.price)}</div>
                    </div>
                    <div class="d-flex align-items-center mx-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm qty-btn" onclick="updateQty(${id}, -1)">-</button>
                        <input type="number" class="form-control form-control-sm qty-input mx-1" value="${item.qty}" min="1" max="${item.stock}" onchange="manualUpdateQty(${id}, this.value)">
                        <button type="button" class="btn btn-outline-secondary btn-sm qty-btn" onclick="updateQty(${id}, 1)">+</button>
                    </div>
                    <div class="text-end" style="width: 80px; flex-shrink: 0;">
                        <div class="fw-bold">Rp ${formatRupiah(itemTotal)}</div>
                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size: 0.8rem;" onclick="removeCartItem(${id})">Hapus</button>
                    </div>
                </div>
            `;

            // Inject hidden inputs for the form
            hiddenInputs.innerHTML += `
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
                <input type="hidden" name="items[${index}][selling_price]" value="${item.price}">
                <input type="hidden" name="items[${index}][discount]" value="0">
            `;
            index++;
        }

        if (index > 0) {
            container.innerHTML = html;
            btnSubmit.disabled = false;
        } else {
            container.innerHTML = '';
            container.appendChild(emptyMsg);
            btnSubmit.disabled = true;
        }

        calculateTotals(subtotal);
    }

    const txtSubtotal = document.getElementById('txtSubtotal');
    const inputDiscount = document.getElementById('inputDiscount');
    const inputTax = document.getElementById('inputTax');
    const txtTotal = document.getElementById('txtTotal');
    const inputPaid = document.getElementById('inputPaid');
    const txtChange = document.getElementById('txtChange');

    let currentGrandTotal = 0;

    function calculateTotals(subtotalAmount = null) {
        let subtotal = 0;
        if (subtotalAmount !== null) {
            subtotal = subtotalAmount;
        } else {
            for (const id in cart) {
                subtotal += cart[id].price * cart[id].qty;
            }
        }

        txtSubtotal.innerText = 'Rp ' + formatRupiah(subtotal);

        const discount = parseRibuan(inputDiscount.value);
        const tax = parseRibuan(inputTax.value);

        currentGrandTotal = subtotal - discount + tax;
        if(currentGrandTotal < 0) currentGrandTotal = 0;

        txtTotal.innerText = 'Rp ' + formatRupiah(currentGrandTotal);

        calculateChange();
    }

    function calculateChange() {
        const paid = parseRibuan(inputPaid.value);
        const change = paid - currentGrandTotal;

        if (change >= 0 && paid > 0) {
            txtChange.value = 'Rp ' + formatRupiah(change);
            txtChange.classList.add('text-success');
            txtChange.classList.remove('text-danger');
        } else if (change < 0 && paid > 0) {
            txtChange.value = '- Rp ' + formatRupiah(Math.abs(change));
            txtChange.classList.add('text-danger');
            txtChange.classList.remove('text-success');
        } else {
            txtChange.value = 'Rp 0';
            txtChange.classList.remove('text-success', 'text-danger');
        }
    }

    // Format initial values
    inputDiscount.value = formatRupiah(parseRibuan(inputDiscount.value));
    inputTax.value = formatRupiah(parseRibuan(inputTax.value));
    inputPaid.value = formatRupiah(parseRibuan(inputPaid.value));
    renderCart();

    // Event Listeners for Totals
    document.querySelectorAll('.nominal-input').forEach(input => {
        input.addEventListener('input', function(e) {
            let val = this.value.replace(/[^,\d]/g, '').toString();
            if (val) {
                this.value = formatRupiah(parseInt(val, 10));
            } else {
                this.value = '0';
            }
            if (this.id === 'inputPaid') {
                calculateChange();
            } else {
                calculateTotals();
            }
        });

        input.addEventListener('focus', function() {
            if (this.value === '0') this.value = '';
        });
        input.addEventListener('blur', function() {
            if (this.value === '') this.value = '0';
        });
    });

    // Payment Logic
    const paymentStatusSelect = document.getElementById('payment_status');
    const dueDateContainer = document.getElementById('dueDateContainer');

    paymentStatusSelect.addEventListener('change', function() {
        if(this.value === 'paid') {
            dueDateContainer.classList.add('d-none');
            document.getElementById('due_date').value = '';

            const currentPaid = parseRibuan(inputPaid.value);
            if (currentPaid === 0 && currentGrandTotal > 0) {
                inputPaid.value = formatRupiah(currentGrandTotal);
                calculateChange();
            }
        } else {
            dueDateContainer.classList.remove('d-none');
        }
    });

    if (paymentStatusSelect.value === 'paid') {
        dueDateContainer.classList.add('d-none');
    }

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
            }
        });
    }

    // Form Submission
    document.getElementById('posForm').addEventListener('submit', function(e) {
        inputDiscount.value = parseRibuan(inputDiscount.value);
        inputTax.value = parseRibuan(inputTax.value);
        inputPaid.value = parseRibuan(inputPaid.value);

        if(paymentStatusSelect.value === 'paid' && inputPaid.value < currentGrandTotal) {
            inputPaid.value = currentGrandTotal;
        }
    });

</script>
@endsection
