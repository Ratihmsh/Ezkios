<?php

$file = __DIR__ . '/resources/views/sales/create.blade.php';
$content = file_get_contents($file);

// Ensure the HTML passed category
$content = preg_replace(
    "/addToCart\(\{\{ \\\$product->id \}\}, '\{\{ addslashes\(\\\$product->name\) \}\}', '\{\{ \\\$product->code \}\}', \{\{ \\\$product->selling_price \}\}, \{\{ \\\$product->stock \}\}\)/",
    "addToCart({{ \$product->id }}, '{{ addslashes(\$product->name) }}', '{{ \$product->code }}', {{ \$product->selling_price }}, {{ \$product->stock }}, '{{ \$product->category }}')",
    $content
);


$js = <<<'JS'
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
                        if (val > bestDiscount) bestDiscount = val;
                    } else if (p.type === 'product_markup') {
                        if (val > bestMarkup) bestMarkup = val;
                    }
                });
                
                if (bestDiscount > 0) {
                    itemDiscount = bestDiscount;
                    promoBadge = `<span class="badge bg-success" style="font-size:0.6rem;">Promo</span>`;
                } else if (bestMarkup > 0) {
                    itemDiscount = -bestMarkup; // Markup is a negative discount
                    promoBadge = `<span class="badge bg-danger" style="font-size:0.6rem;">Markup</span>`;
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
JS;

$content = preg_replace('/<script>.*?<\/script>/is', $js, $content);
file_put_contents($file, $content);

echo "Done\n";
