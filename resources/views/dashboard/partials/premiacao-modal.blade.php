<form action="{{ route('premiacoes.marcar_pago', ['premiacao' => $premiacao->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="mb-3">
            <label for="comprovante" class="form-label">Comprovante de Pagamento (opcional)</label>
            <input type="file" class="form-control" id="comprovante" name="comprovante" accept="image/*">
            <small class="form-text text-muted">Aceita imagens: JPEG, PNG, JPG, GIF, SVG (máx. 2MB)</small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Marcar como Pago</button>
    </div>
</form>
