@extends('layouts.app')

@section('title', "Erro $code")

@section('content')
<div class="container mt-2 text-center mt-5">
    <h1>Erro {{ $code }}</h1>
    <p>Oops! Algo deu errado.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">Voltar para a página inicial</a>
</div>
@endsection