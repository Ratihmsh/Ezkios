<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - {{ $purchase->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-size: 12px;
        }
        .invoice-header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .invoice-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .table-invoice td, .table-invoice th {
            padding: 6px;
            border: 1px solid #dee2e6;
        }
        .total-row {
            font-weight: bold;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="invoice-header">
            <div class="row">
                <div class="col-6">
                    <h2 class="fw-bold">EZKIOS</h2>
                    <p>Jl. EZKIOS No. 1, Jakarta<br>
                    Telp: 021-555-1234<br>
                    Email: info@ezkios.com</p>
                </div>
                <div class="col-6 text-end">
                    <h3>FAKTUR PEMBELIAN</h3>
                    <p>
                        <strong>No. Faktur:</strong> {{ $purchase->invoice_number }}<br>
                        <strong>Tanggal:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}<br>
                        <strong>Status:</strong> {{ $purchase->payment_status_label }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <h6><strong>Supplier:</strong></h6>
                <p>
                    {{ $purchase->supplier->name ?? '-' }}<br>
                    {{ $purchase->supplier->address ?? '-' }}<br>
                    Telp: {{ $purchase->supplier->phone ?? '-' }}
                </p>
            </div>
        </div>

        <table class="table table-invoice">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Beli</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total</strong></td>
                    <td class="text-end">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong>Pajak</strong></td>
                    <td class="text-end">Rp {{ number_format($purchase->tax ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong>Ongkos Kirim</strong></td>
                    <td class="text-end">Rp {{ number_format($purchase->shipping_cost ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-end"><strong>Grand Total</strong></td>
                    <td class="text-end"><strong>Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-3">
            <div class="col-12">
                <p><strong>Catatan:</strong> {{ $purchase->notes ?? '-' }}</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-6">
                <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
            <div class="col-6 text-end">
                <p>Petugas: {{ $purchase->createdBy->name ?? '-' }}</p>
            </div>
        </div>

        <div class="text-center no-print mt-4">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Tutup
            </button>
        </div>
    </div>
</body>
</html>
