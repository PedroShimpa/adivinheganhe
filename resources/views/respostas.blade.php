{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="m-2">
<hr class="my-5">

<h1 class="mb-3">Respostas da adivinhação</h1>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Resposta</th>
                <th>Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach($respostas as $resposta)
                <tr>
                    <td>{{ $resposta->resposta }}</td>
                    <td>{{ $resposta->created_at_br }}</td>
                    
                 
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection