@extends('layouts.app', ['enable_adsense' => false])

@section('content')
<div class="container mt-4">
    <div class="mb-4">
        <h1 class="h3">Rastreamento de Emails</h1>
        <p class="text-white">Acompanhe a abertura e cliques nos emails enviados</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Assunto</th>
                        <th>Enviado em</th>
                        <th>Aberto</th>
                        <th>Cliques</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trackings as $tracking)
                    <tr>
                        <td>{{ $tracking->id }}</td>
                        <td>{{ $tracking->email }}</td>
                        <td>{{ $tracking->subject }}</td>
                        <td>{{ $tracking->sent_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($tracking->opened_at)
                                <span class="badge bg-success">Sim - {{ $tracking->opened_at->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="badge bg-secondary">Não</span>
                            @endif
                        </td>
                        <td>
                            @if($tracking->clicked_links && count($tracking->clicked_links) > 0)
                                <span class="badge bg-info">{{ count($tracking->clicked_links) }} clique(s)</span>
                                <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#clicksModal-{{ $tracking->id }}">Ver</button>
                            @else
                                <span class="badge bg-secondary">0</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Modal for clicks -->
                    @if($tracking->clicked_links && count($tracking->clicked_links) > 0)
                    <div class="modal fade" id="clicksModal-{{ $tracking->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cliques no Email</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <ul class="list-group">
                                        @foreach($tracking->clicked_links as $click)
                                        <li class="list-group-item">
                                            <strong>URL:</strong> {{ $click['url'] }}<br>
                                            <small class="text-muted">Clicado em: {{ \Carbon\Carbon::parse($click['clicked_at'])->format('d/m/Y H:i') }}</small>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </tbody>
            </table>

            {{ $trackings->links() }}
        </div>
    </div>
</div>
@endsection
