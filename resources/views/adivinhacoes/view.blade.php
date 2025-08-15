@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Adivinhação') }}</div>

                <div class="card-body">
                    <form action="{{ route('adivinhacoes.update', $adivinhacao->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="titulo" class="form-label">{{ __('Título') }}</label>
                            <input type="text"
                                class="form-control @error('titulo') is-invalid @enderror"
                                id="titulo"
                                name="titulo"
                                value="{{ old('titulo', $adivinhacao->titulo) }}"
                                required>
                            @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">{{ __('Descrição') }}</label>
                            <textarea
                                class="form-control @error('descricao') is-invalid @enderror"
                                id="descricao"
                                name="descricao"
                                rows="3">{{ old('descricao', $adivinhacao->descricao) }}</textarea>
                            @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="premio" class="form-label">{{ __('Prêmio') }}</label>
                            <input type="text"
                                class="form-control @error('premio') is-invalid @enderror"
                                id="premio"
                                name="premio"
                                value="{{ old('premio', $adivinhacao->premio) }}"
                                required>
                            @error('premio')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="resposta" class="form-label">{{ __('Resposta') }}</label>
                            <input type="text"
                                class="form-control @error('resposta') is-invalid @enderror"
                                id="resposta"
                                name="resposta"
                                value="{{ old('resposta', $adivinhacao->resposta) }}"
                                required>
                            @error('resposta')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label">{{ __('Formato Resposta') }}</label>
                            <input type="text" class="form-control @error('formato_resposta') is-invalid @enderror" id="formato_resposta" name="formato_resposta" value="{{ old('formato_resposta',  $adivinhacao->formato_resposta) }}">
                            @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="imagem" class="form-label">{{ __('Imagem') }}</label>
                            <input class="form-control @error('imagem') is-invalid @enderror"
                                type="file"
                                id="imagem"
                                name="imagem">
                            @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if ($adivinhacao->imagem)
                            <small>Imagem atual: <a href="{{ asset('storage/' . $adivinhacao->imagem) }}" target="_blank">Visualizar</a></small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="dica" class="form-label">{{ __('Dica') }}</label>
                            <input type="text"
                                class="form-control @error('dica') is-invalid @enderror"
                                id="dica"
                                name="dica"
                                value="{{ old('dica', $adivinhacao->dica) }}">
                            @error('dica')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="dica_paga" class="form-label">{{ __('Dica Paga?') }}</label>
                            <select class="form-select @error('dica_paga') is-invalid @enderror"
                                id="dica_paga"
                                name="dica_paga"
                                required>
                                <option value="">Selecione...</option>
                                <option value="S" {{ old('dica_paga', $adivinhacao->dica_paga) == 'S' ? 'selected' : '' }}>Sim</option>
                                <option value="N" {{ old('dica_paga', $adivinhacao->dica_paga) == 'N' ? 'selected' : '' }}>Não</option>
                            </select>
                            @error('dica_paga')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="dica_valor_container" style="display: none;">
                            <label for="dica_valor" class="form-label">{{ __('Valor da Dica (R$)') }}</label>
                            <input type="number"
                                step="0.01"
                                class="form-control @error('dica_valor') is-invalid @enderror"
                                id="dica_valor"
                                name="dica_valor"
                                value="{{ old('dica_valor', $adivinhacao->dica_valor) }}">
                            @error('dica_valor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Enviar Alerta Global?') }}</label>
                            <select class="form-select" name="enviar_alerta_global" required>
                                <option value="S">Sim</option>
                                <option value="N" selected>Não</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="expire_at" class="form-label">{{ __('Expira em') }}</label>
                            <input type="datetime-local"
                                class="form-control @error('expire_at') is-invalid @enderror"
                                id="expire_at"
                                name="expire_at"
                                value="{{ old('expire_at', optional($adivinhacao->expire_at)->format('Y-m-d\TH:i')) }}">
                            @error('expire_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="regiao_id" class="form-label">Região</label>
                            <select name="regiao_id" class="form-select">
                                <option value="">Selecione</option>
                                @foreach($regioes as $regiao)
                                <option value="{{ $regiao->id }}" {{ old('regiao_id', $adivinhacao->regiao_id) == $regiao->id ? 'selected' : '' }}>
                                    {{ $regiao->nome }}
                                </option>
                                @endforeach
                            </select>
                            @error('regiao_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('Salvar Adivinhação') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/ai1aa4q1itml9cxyksgdamr2r0v61fis91gwfmb5bdt9n6df/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#descricao',
            menubar: false,
            height: 300,
            plugins: 'link lists code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            language: 'pt_BR'
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const dicaPagaSelect = document.getElementById('dica_paga');
        const dicaValorContainer = document.getElementById('dica_valor_container');

        function toggleDicaValor() {
            if (dicaPagaSelect.value === 'S') {
                dicaValorContainer.style.display = 'block';
            } else {
                dicaValorContainer.style.display = 'none';
            }
        }

        dicaPagaSelect.addEventListener('change', toggleDicaValor);
        toggleDicaValor();
    });
</script>
@endpush