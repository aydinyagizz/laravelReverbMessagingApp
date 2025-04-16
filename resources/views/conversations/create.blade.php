@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">Yeni Konuşma</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('conversations.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="title" class="form-label">Konuşma Başlığı (Opsiyonel)</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}">
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="participants" class="form-label">Katılımcılar</label>
                                <select multiple class="form-select @error('participants') is-invalid @enderror" id="participants" name="participants[]" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Birden fazla kullanıcı seçmek için Ctrl tuşunu basılı tutun.</div>
                                @error('participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('conversations.index') }}" class="btn btn-secondary">İptal</a>
                                <button type="submit" class="btn btn-primary">Konuşma Oluştur</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
