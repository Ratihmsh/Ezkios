@extends('layouts.app')

@section('title', 'Edit Pengumuman - EZKIOS')
@section('page-title', 'Edit Pengumuman')
@section('page-subtitle', 'Ubah rincian berita/pengumuman')

@section('content')
<div class="row">
    <div class="col-md-8 col-lg-6 mx-auto">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom pt-4 pb-3">
                <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square text-warning"></i> Form Edit Pengumuman</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('news-events.update', $newsEvent->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Pengumuman</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $newsEvent->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Isi Pengumuman</label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="5" required>{{ old('content', $newsEvent->content) }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Gambar/Poster Saat Ini</label>
                        @if($newsEvent->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $newsEvent->image) }}" alt="Poster" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @else
                            <p class="text-muted small">Belum ada gambar</p>
                        @endif
                        <label class="form-label fw-bold mt-2">Ganti Gambar (Opsional)</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">File Lampiran Saat Ini</label>
                        @if($newsEvent->attachment)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $newsEvent->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-file-earmark-text"></i> Lihat Lampiran
                                </a>
                            </div>
                        @else
                            <p class="text-muted small">Belum ada lampiran</p>
                        @endif
                        <label class="form-label fw-bold mt-2">Ganti File Lampiran (Opsional)</label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip">
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Link YouTube (Opsional)</label>
                        <input type="url" name="youtube_link" class="form-control @error('youtube_link') is-invalid @enderror" value="{{ old('youtube_link', $newsEvent->youtube_link) }}" placeholder="Contoh: https://www.youtube.com/watch?v=...">
                        @error('youtube_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $newsEvent->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $newsEvent->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active" value="1" {{ old('is_active', $newsEvent->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="isActive">Status Aktif (Tampilkan)</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('news-events.index') }}" class="btn btn-light border shadow-sm">Batal</a>
                        <button type="submit" class="btn btn-warning shadow-sm fw-bold text-dark"><i class="bi bi-save"></i> Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
