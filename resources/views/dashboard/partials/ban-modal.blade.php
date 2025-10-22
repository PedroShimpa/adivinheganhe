<form action="{{ route('user.ban', ['user' => $user->username]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="mb-3">
            <label for="motivo" class="form-label">Motivo (opcional)</label>
            <textarea name="motivo" id="motivo" class="form-control" rows="3" placeholder="Escreva o motivo do banimento..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-danger">Confirmar Banimento</button>
    </div>
</form>
