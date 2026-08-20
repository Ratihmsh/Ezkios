@extends('layouts.app')

@section('title', __('messages.reports_and_analytics'))
@section('page-title', __('messages.financial_dashboard'))
@section('page-subtitle', __('messages.reports_description'))

@section('content')

<!-- 1. Tiga Kotak Ringkasan Utama -->
<div class="row mb-4">
    <!-- Saldo Uang Real -->
    <div class="col-md-4">
        <div class="card bg-gradient-blue text-white shadow h-100 border-0 rounded-4">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                <div class="bg-white text-primary rounded-circle p-3 mb-3 shadow-sm">
                    <i class="bi bi-wallet2 fs-2"></i>
                </div>
                <h6 class="opacity-75 text-uppercase fw-bold tracking-wide">{{ __('messages.real_cash_balance') }}</h6>
                <h2 class="fw-bolder mb-2">Rp {{ number_format($realCashBalance, 0, ',', '.') }}</h2>
                <div class="mt-2 text-start bg-white bg-opacity-10 p-2 rounded w-100" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="bi bi-box-arrow-in-right"></i> {{ __('messages.capital_portion') }}:</span>
                        <strong class="text-white">Rp {{ number_format($porsiModal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-star"></i> {{ __('messages.profit_portion') }}:</span>
                        <strong class="text-white">Rp {{ number_format($porsiLaba, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mengendap -->
    <div class="col-md-4">
        <div class="card bg-gradient-info text-white shadow h-100 border-0 rounded-4">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                <div class="bg-white text-info rounded-circle p-3 mb-3 shadow-sm">
                    <i class="bi bi-box-seam fs-2"></i>
                </div>
                <h6 class="opacity-75 text-uppercase fw-bold tracking-wide">{{ __('messages.inventory_value') }}</h6>
                <h2 class="fw-bolder mb-2">Rp {{ number_format($inventoryValue, 0, ',', '.') }}</h2>
                <small class="opacity-75">{{ __('messages.inventory_valuation') }}</small>
            </div>
        </div>
    </div>

    <!-- Hutang & Piutang -->
    <div class="col-md-4">
        <div class="card bg-gradient-orange text-white shadow h-100 border-0 rounded-4">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                <div class="bg-white text-warning rounded-circle p-3 mb-3 shadow-sm">
                    <i class="bi bi-arrow-left-right fs-2"></i>
                </div>
                <h6 class="opacity-75 text-uppercase fw-bold tracking-wide">{{ __('messages.payables_and_receivables') }}</h6>

                <div class="w-100 mt-2 text-start bg-white bg-opacity-25 p-2 rounded" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-between mb-2 border-bottom border-white border-opacity-10 pb-1">
                        <span><i class="bi bi-arrow-down-right"></i> {{ __('messages.accounts_receivable') }}:</span>
                        <strong class="text-white">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><i class="bi bi-arrow-up-right"></i> {{ __('messages.accounts_payable') }}:</span>
                        <strong class="text-white">Rp {{ number_format($totalHutang, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-auto">
                <label class="form-label mb-0 fw-bold"><i class="bi bi-calendar-range"></i> {{ __('messages.report_period') }}:</label>
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-auto">
                <span>{{ __('messages.to') }}</span>
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-auto ms-auto">
                <a href="{{ route('reports.export-excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success fw-bold text-white shadow-sm" style="border-radius: 8px;">
                    <i class="bi bi-file-earmark-excel"></i> Ekspor Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 2 & 3. Grafik -->
<div class="row">
    <!-- Grafik Laba Per Hari (Line Chart) -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold text-secondary"><i class="bi bi-graph-up text-primary"></i> {{ __('messages.daily_net_profit_trend') }}</h5>
                <small class="text-muted">{{ __('messages.profit_movement') }}</small>
            </div>
            <div class="card-body">
                <canvas id="dailyProfitChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Grafik Barang Terlaris (Bar Chart) -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold text-secondary"><i class="bi bi-trophy text-warning"></i> {{ __('messages.top_selling_items') }}</h5>
                <small class="text-muted">{{ __('messages.top_products_interest') }}</small>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="topProductsChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 4. Daftar Arus Kas -->
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-list-columns-reverse"></i> {{ __('messages.cash_flow_details') }} <small>({{ __('messages.based_on_date_filter') }})</small></h5>
    <div>
        <a href="{{ route('cash-flow.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-lg"></i> {{ __('messages.add_cash_transaction') }}
        </a>
    </div>
</div>
<div class="row mb-5">
    <!-- Tabel Uang Masuk -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-4">
            <div class="card-header bg-success text-white border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-down-circle"></i> {{ __('messages.incoming_cash_list') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="position: sticky; top: 0; z-index: 10; background-color: #f8f9fa;">
                            <tr>
                                <th class="ps-3 py-2 border-bottom" style="width: 15%;">{{ __('messages.date') }}</th>
                                <th class="py-2 border-bottom">{{ __('messages.category') }}</th>
                                <th class="text-end py-2 border-bottom text-nowrap" style="width: 25%;">{{ __('messages.amount') }}</th>
                                <th class="text-center pe-3 py-2 border-bottom" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashInList as $in)
                            <tr>
                                <td class="ps-3 text-muted text-nowrap">{{ $in->transaction_date->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $in->category }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($in->description, 23) }}</small>
                                </td>
                                <td class="text-end text-success fw-bold text-nowrap">Rp {{ number_format($in->amount, 0, ',', '.') }}</td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('cash-flow.show', $in->id) }}" class="btn btn-sm btn-outline-info rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_incoming_cash') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Uang Keluar -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-4">
            <div class="card-header bg-danger text-white border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-up-circle"></i> {{ __('messages.outgoing_cash_list') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="position: sticky; top: 0; z-index: 10; background-color: #f8f9fa;">
                            <tr>
                                <th class="ps-3 py-2 border-bottom" style="width: 15%;">{{ __('messages.date') }}</th>
                                <th class="py-2 border-bottom">{{ __('messages.category') }}</th>
                                <th class="text-end py-2 border-bottom text-nowrap" style="width: 25%;">{{ __('messages.amount') }}</th>
                                <th class="text-center pe-3 py-2 border-bottom" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashOutList as $out)
                            <tr>
                                <td class="ps-3 text-muted">{{ $out->transaction_date->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $out->category }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($out->description, 25) }}</small>
                                </td>
                                <td class="text-end text-danger fw-bold text-nowrap">Rp {{ number_format($out->amount, 0, ',', '.') }}</td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('cash-flow.show', $out->id) }}" class="btn btn-sm btn-outline-info rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_outgoing_cash') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script for Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- 1. Line Chart: Laba Per Hari ---
        const dailyCtx = document.getElementById('dailyProfitChart');
        if (dailyCtx) {
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartDates) !!},
                    datasets: [{
                        label: '{{ __('messages.net_profit_label') }}',
                        data: {!! json_encode($chartProfits) !!},
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(13, 110, 253)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4 // curve
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000 || value <= -1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                    } else if (value >= 1000 || value <= -1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- 2. Bar Chart: Barang Terlaris ---
        const topCtx = document.getElementById('topProductsChart');
        if (topCtx) {
            new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topProductNames) !!},
                    datasets: [{
                        label: '{{ __('messages.sold_label') }}',
                        data: {!! json_encode($topProductQtys) !!},
                        backgroundColor: [
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(13, 202, 240, 0.8)',
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(13, 110, 253, 0.8)',
                            'rgba(102, 16, 242, 0.8)',
                            'rgba(214, 51, 132, 0.8)'
                        ],
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y', // horizontal bar chart
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
