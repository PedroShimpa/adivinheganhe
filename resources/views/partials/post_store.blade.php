    <div class="card shadow-lg border-0 mb-5">
        <div class="card-body">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <textarea name="content" class="form-control @error('content') is-invalid @enderror mb-3"
                    rows="3" placeholder="O que você está pensando?">{{ old('content') }}</textarea>
                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror

                <input type="file" name="file" class="form-control mb-3 @error('file') is-invalid @enderror">
                @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>