@extends('layouts.app')

@section('title', 'Kasir')
@section('page-title', 'Kasir ')
@section('page-subtitle', 'Pilih produk dan selesaikan transaksi')

@section('content')
<style>
    .pos-layout {
        display: flex;
        gap: 20px;
        height: calc(100vh - 120px);
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
    /* Fullscreen Mode */
    body.pos-fullscreen .sidebar {
        display: none !important;
    }
    body.pos-fullscreen .main-content {
        margin-left: 0 !important;
        padding: 10px !important;
    }
    body.pos-fullscreen .main-content > .d-flex.mb-4 {
        display: none !important;
    }
    body.pos-fullscreen .pos-layout {
        height: calc(100vh - 20px) !important;
    }
</style>

<div class="pos-layout">
    <!-- Left: Catalog -->
    <div class="pos-catalog">
        <div class="mb-3 d-flex gap-2">
            <input type="text" id="searchProduct" class="form-control form-control-lg" placeholder="Cari berdasarkan nama atau kode produk..." autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" onclick="document.body.classList.toggle('pos-fullscreen')" title="Mode Fullscreen">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                    <div class="card product-card" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->code }}', {{ $product->selling_price }}, {{ $product->stock }}, '{{ $product->category }}')">
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
        <form action="{{ route('sales.store') }}" method="POST" id="posForm" class="d-flex flex-column h-100">
            @csrf
            <h5 class="border-bottom pb-2"><i class="bi bi-cart3"></i> Keranjang Belanja</h5>

            <div class="cart-items" id="cartContainer">
                <div class="text-center text-muted py-5" id="emptyCartMsg">
                    Keranjang masih kosong
                </div>
                <!-- Cart items will be injected here -->
            </div>

            <!-- Hidden Transaction Details -->
            <input type="hidden" name="invoice_number" value="{{ $invoiceNumber }}">
            <input type="hidden" name="sale_date" value="{{ date('Y-m-d') }}">

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span id="txtSubtotal">Rp 0</span>
                </div>
                <div class="totals-row">
                    <span>Diskon (Rp)</span>
                    <input type="text" id="inputDiscount" name="discount" class="form-control form-control-sm w-25 text-end nominal-input" value="0">
                </div>
                <div class="totals-row">
                    <span>Pajak (Rp)</span>
                    <input type="text" id="inputTax" name="tax" class="form-control form-control-sm w-25 text-end nominal-input" value="0">
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
                            <option value="paid" selected>Lunas</option>
                            <option value="partial">Bayar Sebagian</option>
                            <option value="pending">Belum Dibayar</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-0 small">Metode <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select form-select-sm" required>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm }}">{{ $pm }}</option>
                            @endforeach
                            <option value="Lainnya_Baru">+ Tambah Baru...</option>
                        </select>
                        <input type="text" name="payment_method_new" id="payment_method_new" class="form-control form-control-sm mt-1 d-none" placeholder="Metode Baru">
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-12">
                        <label class="form-label mb-0 small">Kode Kupon / Promo (Opsional)</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="applied_promo_code" id="inputPromoCode" class="form-control" placeholder="Masukkan kode promo">
                            <button type="button" class="btn btn-outline-primary" id="btnApplyPromo">Pakai</button>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12 d-none" id="dueDateContainer">
                        <label class="form-label mb-0 small">Jatuh Tempo</label>
                        <input type="date" name="due_date" id="due_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-6" id="paidAmountContainer">
                        <label class="form-label mb-0 small">Uang Diterima</label>
                        <input type="text" name="paid_amount" id="inputPaid" class="form-control form-control-sm text-end nominal-input" value="0">
                    </div>
                    <div class="col-6" id="changeAmountContainer">
                        <label class="form-label mb-0 small">Kembalian</label>
                        <input type="text" id="txtChange" class="form-control form-control-sm text-end bg-light" readonly value="Rp 0">
                    </div>
                </div>
            </div>

            <!-- Container for dynamic hidden inputs for cart items -->
            <div id="hiddenInputsContainer"></div>

            <button type="submit" class="btn btn-success btn-lg w-100 mt-4" id="btnSubmit" disabled>
                <i class="bi bi-check-circle"></i> Simpan Transaksi
            </button>
        </form>
    </div>
</div>

<script>
    // Promotions
    const activePromotions = @json($activePromotions);
    let currentGlobalPromo = null;

    // Cart State
    let cart = {};

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID').format(number);
    };

    const parseRibuan = (str) => {
        if (!str) return 0;
        return parseInt(str.toString().replace(/[^0-9]/g, ''), 10) || 0;
    };

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
    function addToCart(id, name, code, price, stock, category) {
        if (stock <= 0) {
            alert('Stok produk habis!');
            return;
        }

        if (cart[id]) {
            if (cart[id].qty < stock) {
                cart[id].qty++;
            } else {
                alert('Stok tidak mencukupi!');
            }
        } else {
            cart[id] = { id, name, code, price, qty: 1, stock, category };
        }
        renderCart();
    }

    // Expose functions to global scope for inline handlers
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

    function isPromoValid(p) {
        const pm = document.getElementById('payment_method').value;
        const codeInput = document.getElementById('inputPromoCode');
        const code = codeInput ? codeInput.value.trim().toLowerCase() : '';

        // Check promo_code
        if (p.promo_code && p.promo_code.toLowerCase() !== code) return false;

        // Check payment_method
        if (p.payment_method && p.payment_method.toLowerCase() !== pm.toLowerCase()) return false;

        return true;
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        const hiddenInputs = document.getElementById('hiddenInputsContainer');
        const btnSubmit = document.getElementById('btnSubmit');
        
        container.innerHTML = '';
        hiddenInputs.innerHTML = '';

        let subtotal = 0;
        let html = '';
        let index = 0;

        for (const id in cart) {
            const item = cart[id];
            
            let itemDiscount = 0;
            let promoBadge = '';
            
            const itemPromos = activePromotions.filter(p => 
                isPromoValid(p) && 
                (
                    (p.type === 'product_discount' && p.product_id == id) ||
                    (p.type === 'product_markup' && p.product_id == id) ||
                    (p.type === 'category_discount' && p.category_name == item.category)
                ) && 
                item.qty >= p.min_qty
            );
            
            if (itemPromos.length > 0) {
                let bestDiscount = 0;
                let bestMarkup = 0;
                
                itemPromos.forEach(p => {
                    let val = p.value_type === 'percentage' ? (item.price * p.value / 100) : parseFloat(p.value);
                    if (p.type === 'product_discount' || p.type === 'category_discount') {
                        if (val > bestDiscount) { bestDiscount = val; appliedPromoId = p.id; }
                    } else if (p.type === 'product_markup') {
                        if (val > bestMarkup) { bestMarkup = val; appliedPromoId = p.id; }
                    }
                });
                
                if (bestDiscount > 0) {
                    itemDiscount = bestDiscount;
                    promoBadge = `<span class="badge bg-success" style="font-size:0.6rem;">Promo</span>`;
                    hiddenInputs.innerHTML += `<input type="hidden" name="applied_promotions[]" value="${appliedPromoId}">`;
                } else if (bestMarkup > 0) {
                    itemDiscount = -bestMarkup; // Markup is a negative discount
                    promoBadge = `<span class="badge bg-danger" style="font-size:0.6rem;">Markup</span>`;
                    hiddenInputs.innerHTML += `<input type="hidden" name="applied_promotions[]" value="${appliedPromoId}">`;
                }
            }

            const effectivePrice = item.price - itemDiscount;
            const itemTotal = effectivePrice * item.qty;
            subtotal += itemTotal;

            html += `
                <div class="cart-item">
                    <div style="flex: 1; min-width: 0;">
                        <div class="cart-item-title text-truncate" title="${item.name}">${item.name} ${promoBadge}</div>
                        <div class="cart-item-price text-success">
                            ${itemDiscount > 0 ? `<small class="text-muted text-decoration-line-through">Rp ${formatRupiah(item.price)}</small> ` : ''}
                            Rp ${formatRupiah(effectivePrice)}
                        </div>
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
                <input type="hidden" name="items[${index}][discount]" value="${itemDiscount * item.qty}">
            `;
            index++;
            
            // Check for buy_x_get_y
            const bxgyPromos = activePromotions.filter(p => isPromoValid(p) && p.type === 'buy_x_get_y' && p.product_id == id && item.qty >= p.min_qty);
            bxgyPromos.forEach(p => {
                const multiplier = Math.floor(item.qty / p.min_qty);
                const totalRewardQty = multiplier * p.reward_qty;
                if (totalRewardQty > 0 && p.reward_product) {
                    html += `
                    <div class="cart-item bg-light border-0 py-1 px-2 mt-1 rounded">
                        <div style="flex: 1; min-width: 0;">
                            <div class="cart-item-title text-truncate text-primary" style="font-size: 0.8rem;"><i class="bi bi-gift"></i> GRATIS: ${p.reward_product.name}</div>
                        </div>
                        <div class="d-flex align-items-center mx-2 text-primary fw-bold" style="font-size: 0.8rem;">
                            ${totalRewardQty}x
                        </div>
                        <div class="text-end" style="width: 80px; flex-shrink: 0; font-size: 0.8rem;">
                            <div class="fw-bold text-muted">Rp 0</div>
                        </div>
                    </div>
                    `;
                    // Inject free item to hidden inputs for backend to process and reduce stock
                    hiddenInputs.innerHTML += `
                        <input type="hidden" name="items[${index}][product_id]" value="${p.reward_product_id}">
                        <input type="hidden" name="items[${index}][quantity]" value="${totalRewardQty}">
                        <input type="hidden" name="items[${index}][selling_price]" value="0">
                        <input type="hidden" name="items[${index}][discount]" value="0">
                        <input type="hidden" name="applied_promotions[]" value="${p.id}">
                    `;
                    index++;
                }
            });
        }

        if (index > 0) {
            container.innerHTML = html;
            btnSubmit.disabled = false;
        } else {
            container.innerHTML = '<div class="text-center text-muted py-5" id="emptyCartMsg">Keranjang masih kosong</div>';
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
                const item = cart[id];
                let itemDiscount = 0;
                let bestDiscount = 0;
                let bestMarkup = 0;
                
                const itemPromos = activePromotions.filter(p => 
                    isPromoValid(p) && 
                    (
                        (p.type === 'product_discount' && p.product_id == id) ||
                        (p.type === 'product_markup' && p.product_id == id) ||
                        (p.type === 'category_discount' && p.category_name == item.category)
                    ) && 
                    item.qty >= p.min_qty
                );
                
                itemPromos.forEach(p => {
                    let val = p.value_type === 'percentage' ? (item.price * p.value / 100) : parseFloat(p.value);
                    if ((p.type === 'product_discount' || p.type === 'category_discount') && val > bestDiscount) bestDiscount = val;
                    if (p.type === 'product_markup' && val > bestMarkup) bestMarkup = val;
                });
                if (bestDiscount > 0) itemDiscount = bestDiscount;
                else if (bestMarkup > 0) itemDiscount = -bestMarkup;
                
                subtotal += (item.price - itemDiscount) * item.qty;
            }
        }

        txtSubtotal.innerText = 'Rp ' + formatRupiah(subtotal);

        // Check Global Transaction Discount
        let globalPromoVal = 0;
        currentGlobalPromo = null;
        const globalPromos = activePromotions.filter(p => isPromoValid(p) && p.type === 'transaction_discount' && subtotal >= p.min_spend);
        if(globalPromos.length > 0) {
            globalPromos.forEach(p => {
                let val = p.value_type === 'percentage' ? (subtotal * p.value / 100) : parseFloat(p.value);
                if (val > globalPromoVal) {
                    globalPromoVal = val;
                    currentGlobalPromo = p;
                }
            });
        }
        
        let existingPromoAlert = document.getElementById('promoAlert');
        let existingGlobalPromoInput = document.getElementById('globalPromoInput');
        if (existingGlobalPromoInput) existingGlobalPromoInput.remove();

        if (globalPromoVal > 0) {
            inputDiscount.value = formatRupiah(globalPromoVal);
            inputDiscount.readOnly = true; // Auto apply, disable manual edit
            if (!existingPromoAlert) {
                const promoHtml = `<div class="alert alert-success py-1 px-2 mb-2" id="promoAlert" style="font-size: 0.8rem;">
                                     <i class="bi bi-tag-fill"></i> Promo <strong>${currentGlobalPromo.name}</strong> diterapkan!
                                   </div>`;
                document.querySelector('.totals-section').insertAdjacentHTML('afterbegin', promoHtml);
            } else {
                existingPromoAlert.innerHTML = `<i class="bi bi-tag-fill"></i> Promo <strong>${currentGlobalPromo.name}</strong> diterapkan!`;
            }
            document.getElementById('hiddenInputsContainer').innerHTML += `<input type="hidden" id="globalPromoInput" name="applied_promotions[]" value="${currentGlobalPromo.id}">`;
        } else {
            inputDiscount.readOnly = false;
            if (existingPromoAlert) existingPromoAlert.remove();
        }

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

        // Auto select all on click
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
    const paidAmountContainer = document.getElementById('paidAmountContainer');
    const changeAmountContainer = document.getElementById('changeAmountContainer');
    const lblPaid = paidAmountContainer.querySelector('label');

    function updatePaymentUI() {
        if(paymentStatusSelect.value === 'paid') {
            dueDateContainer.classList.add('d-none');
            document.getElementById('due_date').value = '';

            paidAmountContainer.classList.remove('d-none');
            changeAmountContainer.classList.remove('d-none');
            lblPaid.innerText = 'Uang Diterima';

            // Auto fill paid amount with grand total if not filled
            const currentPaid = parseRibuan(inputPaid.value);
            if (currentPaid === 0 && currentGrandTotal > 0) {
                inputPaid.value = formatRupiah(currentGrandTotal);
                calculateChange();
            }
        } else if (paymentStatusSelect.value === 'partial') {
            dueDateContainer.classList.remove('d-none');

            paidAmountContainer.classList.remove('d-none');
            changeAmountContainer.classList.remove('d-none');
            lblPaid.innerText = 'Jumlah Dibayar';
        } else {
            dueDateContainer.classList.remove('d-none');

            paidAmountContainer.classList.add('d-none');
            changeAmountContainer.classList.add('d-none');
            inputPaid.value = '0';
            calculateChange();
        }
    }

    paymentStatusSelect.addEventListener('change', updatePaymentUI);

    // Initialize state
    updatePaymentUI();

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
            // Trigger recalculate in case promo depends on PM
            renderCart();
        });
    }

    const btnApplyPromo = document.getElementById('btnApplyPromo');
    if (btnApplyPromo) {
        btnApplyPromo.addEventListener('click', function() {
            renderCart();
        });
    }

    // Form Submission formatting cleanup
    document.getElementById('posForm').addEventListener('submit', function(e) {
        // Parse nominal inputs back to normal numbers before submit
        inputDiscount.value = parseRibuan(inputDiscount.value);
        inputTax.value = parseRibuan(inputTax.value);
        inputPaid.value = parseRibuan(inputPaid.value);

        // If paid, ensure paid_amount is at least grand total if they didn't fill it correctly
        if(paymentStatusSelect.value === 'paid' && inputPaid.value < currentGrandTotal) {
            inputPaid.value = currentGrandTotal;
        }
    });

</script>
@endsection
