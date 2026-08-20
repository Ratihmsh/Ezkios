<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk Spesial</title>
    <!-- Google Fonts untuk tampilan premium -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 12mm 10mm; /* Margin sedikit lebih lebar di atas/bawah */
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #1f2937;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ----- HEADER KATALOG ----- */
        .catalog-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 15px;
            margin-bottom: 25px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header-title h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #111827;
            text-transform: uppercase;
        }
        .header-title p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        .header-meta {
            text-align: right;
        }
        .header-meta .date {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        .header-meta .count {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        /* ----- GRID PRODUK ----- */
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
        }

        /* ----- CARD PRODUK ----- */
        .product-card {
            background: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            padding: 12px;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); /* Soft shadow untuk kesan premium */
        }

        .product-image-wrapper {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 12px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .product-image-placeholder {
            color: #9ca3af;
            font-size: 12px;
            font-weight: 500;
        }

        /* ----- INFO PRODUK ----- */
        .product-info {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .product-category {
            display: inline-block;
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 10px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 9999px;
            margin-bottom: 8px;
            align-self: flex-start;
        }
        .product-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 4px 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-sku {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        /* ----- HARGA ----- */
        .product-price-wrapper {
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px dashed #e5e7eb;
        }
        .product-price {
            font-size: 16px;
            font-weight: 700;
            color: #059669; /* Warna hijau premium */
            margin: 0;
        }

        /* ----- TOMBOL PRINT ----- */
        .print-btn-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
        }
        .print-btn {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
        }
        .print-btn:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        @media print {
            .print-btn-container {
                display: none;
            }
            /* Menghilangkan shadow saat diprint agar lebih bersih di kertas */
            .product-card {
                box-shadow: none;
                border-color: #e5e7eb;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button class="print-btn" onclick="window.print()">
            <svg style="width:18px;height:18px;vertical-align:text-bottom;margin-right:8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Katalog Sekarang
        </button>
        <p style="font-size: 13px; color: #64748b; margin-top: 12px;">Pastikan pengaturan kertas diatur ke <strong>A4</strong> dan centang opsi <strong>"Background graphics"</strong> untuk hasil maksimal.</p>
    </div>

    <!-- HEADER PREMIUM -->
    <div class="catalog-header">
        <div class="header-title">
            <h1>KATALOG PRODUK</h1>
            {{-- <p>Daftar harga dan ketersediaan barang</p> --}}
        </div>
        {{-- <div class="header-meta">
            <div class="date">{{ date('d F Y') }}</div>
            <div class="count">{{ $products->count() }} Produk Tersedia</div>
        </div> --}}
    </div>

    <!-- GRID PREMIUM -->
    <div class="catalog-grid">
        @foreach($products as $product)
        <div class="product-card">

            <div class="product-image-wrapper">
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="product-image">
                @else
                    <span class="product-image-placeholder">Tanpa Foto</span>
                @endif
            </div>

            <div class="product-info">
                {{-- <span class="product-category">{{ $product->category }}</span> --}}
                <h3 class="product-name">{{ $product->name }}</h3>
                @if($product->sku)
                <div class="product-sku">SKU: {{ $product->sku }}</div>
                @endif
            </div>

            <div class="product-price-wrapper">
                <p class="product-price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
            </div>

        </div>
        @endforeach
    </div>

    @if($products->isEmpty())
    <div style="text-align: center; margin-top: 80px; padding: 40px; background: #f8fafc; border-radius: 12px;">
        <svg style="width:48px;height:48px;color:#cbd5e1;margin-bottom:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
        <h3 style="margin:0;color:#64748b;font-weight:500;">Tidak ada produk aktif yang ditemukan.</h3>
    </div>
    @endif

</body>
</html>
