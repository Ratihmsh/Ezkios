@extends('layouts.app')

@section('title', 'Setting Permission')
@section('page-title', 'Setting Permission untuk ' . $role->display_name)
@section('page-subtitle', 'Atur permission yang dimiliki role ini')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('roles.update-permissions', $role) }}" method="POST">
            @csrf

            <div class="row">
                @foreach($permissions as $group => $perms)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>{{ $group }}</strong>
                        </div>
                        <div class="card-body">
                            @foreach($perms as $permission)
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input" id="perm-{{ $permission->id }}"
                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                    {{ $permission->display_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Permission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
