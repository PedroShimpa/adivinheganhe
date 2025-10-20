@extends('layouts.app')

@section('content')
<div class="container mb-5 mt-2">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Cadastrar Adivinhação') }}</div>

                <div class="card-body">
                    <form action="{{ route('adivinhacoes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="titulo" class="form-label">{{ __('Título') }}</label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo') }}" required>
                            @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">{{ __('Descrição') }}</label>
                            <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="3">{!! old('descricao') !!}</textarea>
                            @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="premio" class="form-label">{{ __('Prêmio') }}</label>
                            <input type="text" class="form-control @error('premio') is-invalid @enderror" id="premio" name="premio" value="{{ old('premio') }}" required>
                            @error('premio')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="resposta" class="form-label">{{ __('Resposta') }}</label>
                            <input type="text" class="form-control @error('resposta') is-invalid @enderror" id="resposta" name="resposta" value="{{ old('resposta') }}" required>
                            @error('resposta')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label">{{ __('Formato Resposta') }}</label>
                            <input type="text" class="form-control @error('formato_resposta') is-invalid @enderror" id="formato_resposta" name="formato_resposta" value="{{ old('formato_resposta') }}">
                            @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="imagem" class="form-label">{{ __('Imagem') }}</label>
                            <input class="form-control @error('imagem') is-invalid @enderror" type="file" id="imagem" name="imagem" required>
                            @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="dica" class="form-label">{{ __('Dica') }}</label>
                            <input type="text" class="form-control @error('dica') is-invalid @enderror" id="dica" name="dica" value="{{ old('dica') }}">
                            @error('dica')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="dica_paga" class="form-label">{{ __('Dica Paga?') }}</label>
                            <select class="form-select @error('dica_paga') is-invalid @enderror" id="dica_paga" name="dica_paga" required>
                                <option value="">Selecione...</option>
                                <option value="S" {{ old('dica_paga') == 'S' ? 'selected' : '' }}>Sim</option>
                                <option value="N" {{ old('dica_paga') == 'N' ? 'selected' : '' }} selected>Não</option>
                            </select>
                            @error('dica_paga')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="dica_valor_container" style="display: none;">
                            <label for="dica_valor" class="form-label">{{ __('Valor da Dica (R$)') }}</label>
                            <input type="number" step="0.01" class="form-control @error('dica_valor') is-invalid @enderror" id="dica_valor" name="dica_valor" value="{{ old('dica_valor') }}">
                            @error('dica_valor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
            

                        <div class="mb-3">
                            <label for="liberado_at" class="form-label">{{ __('Liberar em') }}</label>
                            <input type="datetime-local" class="form-control @error('liberado_at') is-invalid @enderror" id="liberado_at" name="liberado_at" value="{{ old('liberado_at', now()->format('Y-m-d\TH:i')) }}">
                            @error('liberado_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expire_at" class="form-label">{{ __('Expira em') }}</label>
                            <input type="datetime-local" class="form-control @error('expire_at') is-invalid @enderror" id="expire_at" name="expire_at" value="{{ old('expire_at', now()->addDays(3)->format('Y-m-d\TH:i')) }}">
                            @error('expire_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="dificuldade" class="form-label">{{ __('Dificuldade') }}</label>
                            <select class="form-select @error('dificuldade') is-invalid @enderror" id="dificuldade" name="dificuldade" required>
                                <option value="">Selecione...</option>
                                <option value="muito facil" {{ old('dificuldade') == 'muito facil' ? 'selected' : '' }}>Muito Fácil</option>
                                <option value="facil" {{ old('dificuldade') == 'facil' ? 'selected' : '' }}>Fácil</option>
                                <option value="média" {{ old('dificuldade') == 'média' ? 'selected' : '' }} selected>Média</option>
                                <option value="dificil" {{ old('dificuldade') == 'dificil' ? 'selected' : '' }}>Difícil</option>
                                <option value="muito dificil" {{ old('dificuldade') == 'muito dificil' ? 'selected' : '' }}>Muito Difícil</option>
                            </select>
                            @error('dificuldade')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="vip_release_at" class="form-label">{{ __('Liberar para VIPs em') }}</label>
                            <input type="datetime-local" class="form-control @error('vip_release_at') is-invalid @enderror" id="vip_release_at" name="vip_release_at" value="{{ old('vip_release_at') }}">
                            @error('vip_release_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Deixe em branco para liberar imediatamente para todos</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="only_members" id="only_members" value="S" {{ old('only_members') ? 'checked' : '' }}>
                                <label class="form-check-label" for="only_members">
                                    Apenas para membros (membros podem responder, todos podem ver)
                                </label>
                            </div>
                        </div>

                      

                        <div class="mb-3">
                            <label class="form-label">{{ __('Notificações (canal)') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_whatsapp" id="notificar_whatsapp" value="1" {{ old('notificar_whatsapp') ? 'checked' : 'checked' }}>
                                <label class="form-check-label" for="notificar_whatsapp">
                                    WhatsApp
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_email" id="notificar_email" value="1" {{ old('notificar_email') ? 'checked' : 'checked' }}>
                                <label class="form-check-label" for="notificar_email">
                                    E-mail
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_push" id="notificar_push" value="1" {{ old('notificar_push') ? 'checked' : 'checked' }}>
                                <label class="form-check-label" for="notificar_push">
                                    Push Notification
                                </label>
                            </div>
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
        const dicaValor = document.getElementById('dica_valor_container');

        function toggleDicaValor() {
            if (dicaPagaSelect.value === 'S') {
                dicaValor.style.display = 'block';
            } else {
                dicaValor.style.display = 'none';
            }
        }

        dicaPagaSelect.addEventListener('change', toggleDicaValor);
        toggleDicaValor();
    });
</script>
@endpush