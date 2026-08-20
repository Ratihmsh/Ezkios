@extends('layouts.app')

@section('title', 'Detail Transaksi Kas')
@section('page-title', 'Detail Transaksi Kas')
@section('page-subtitle', 'Informasi lengkap transaksi')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="bi bi-wallet2"></i> <strong>Detail Transaksi</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table">
                    <tr>
                        <th style="width: 35%;">Tipe Transaksi</th>
                        <td>
                            <span class="badge bg-{{ $cashFlow->type_color }} fs-6 p-2">
                                {{ $cashFlow->type_label }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $cashFlow->category }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah</th>
                        <td class="{{ $cashFlow->type == 'income' ? 'text-success' : 'text-danger' }} fs-5">
                            <strong>Rp {{ number_format($cashFlow->amount, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Transaksi</th>
                        <td>{{ $cashFlow->transaction_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td>{{ $cashFlow->payment_method ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table">

                    <tr>
                        <th>Dibuat Oleh</th>
                        <td>{{ $cashFlow->createdBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate Oleh</th>
                        <td>{{ $cashFlow->updatedBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $cashFlow->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate</th>
                        <td>{{ $cashFlow->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mb-3">
            <label class="fw-bold">Deskripsi</label>
            <p>{{ $cashFlow->description ?? '-' }}</p>
        </div>

        @if($cashFlow->attachment)
        <div class="mb-3">
            <label class="fw-bold">Bukti Transaksi</label>
            <div class="mt-2">
                @php
                    $ext = pathinfo($cashFlow->attachment, PATHINFO_EXTENSION);
                @endphp
                @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                    <img src="{{ asset('storage/' . $cashFlow->attachment) }}" alt="Bukti Transaksi" class="img-fluid rounded" style="max-height: 300px;">
                @else
                    <a href="{{ asset('storage/' . $cashFlow->attachment) }}" target="_blank" class="btn btn-info">
                        <i class="bi bi-file-earmark-pdf"></i> Lihat Bukti (PDF)
                    </a>
                @endif
            </div>
        </div>
        @endif

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            {{-- <div>
                <a href="{{ route('cash-flow.edit', $cashFlow) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('cash-flow.destroy', $cashFlow) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div> --}}
        </div>
    </div>
</div>
@endsection
