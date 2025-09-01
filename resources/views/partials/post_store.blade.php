<div class="card shadow-lg border-0 rounded-4 mb-5">
    <div class="card-body">
        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Campo de texto -->
            <div class="form-outline mb-3">
                <textarea 
                    name="content" 
                    id="content" 
                    class="form-control @error('content') is-invalid @enderror" 
                    rows="3">{{ old('content') }}</textarea>
                <label class="form-label" for="content">O que você está pensando?</label>
                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Upload de arquivo estilizado -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="file-upload-wrapper position-relative">
                    <input 
                        type="file" 
                        name="file" 
                        id="file" 
                        accept="image/*"
                        class="file-upload-input d-none @error('file') is-invalid @enderror" 
                        onchange="previewFile(this)">
                    
                    <label for="file" class="btn btn-light border px-3 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-paperclip me-2"></i> Anexar arquivo
                    </label>
                    
                    @error('file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <span id="file-name" class="text-muted small fst-italic">Nenhum arquivo selecionado</span>
            </div>

            <!-- Pré-visualização -->
            <div id="preview-container" class="mb-3 d-none">
                <img id="preview-image" src="" class="img-fluid rounded-3 shadow-sm" style="max-height: 200px; object-fit: cover;" />
            </div>

            <!-- Botão de publicar -->
            <div class="text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bi bi-send-fill me-1"></i> Publicar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script para preview -->
<script>
    function previewFile(input) {
        const file = input.files[0];
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');
        const fileName = document.getElementById('file-name');

        if (file) {
            fileName.innerText = file.name;

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewContainer.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            fileName.innerText = "Nenhum arquivo selecionado";
            previewContainer.classList.add('d-none');
            previewImage.src = "";
        }
    }
</script>
