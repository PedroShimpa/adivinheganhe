@forelse($respostas as $resposta)
    <tr>
        <td class="fw-semibold text-center">{{ $resposta->uuid }}</td>
        <td class="fw-semibold">{{ $resposta->username }}</td>
        <td>{{ $resposta->resposta }}</td>
        <td class="text-muted text-center">{{ $resposta->created_at_br }}</td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center text-muted">Acabou! Você viu tudo...</td>
    </tr>
@endforelse
