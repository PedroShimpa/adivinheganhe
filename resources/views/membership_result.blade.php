@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white shadow-lg rounded-lg p-8">
            @if(session('success'))
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        {{ __('Bem-vindo ao Clube VIP!') }}
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        {{ session('success') }}
                    </p>
                    <div class="mt-8">
                        <p class="text-sm text-gray-700 mb-4">
                            Você agora tem acesso a benefícios exclusivos:
                        </p>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• 7 tentativas diárias (em vez de 3)</li>
                            <li>• Acesso antecipado a adivinhações VIP</li>
                            <li>• Participação em eventos especiais</li>
                            <li>• Suporte prioritário</li>
                            <li>• Badge VIP em seu perfil</li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('home') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Explorar Adivinhações') }}
                        </a>
                    </div>
                </div>
            @elseif(session('error'))
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        {{ __('Erro no Pagamento') }}
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        {{ session('error') }}
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('membership.index') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Tentar Novamente') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        {{ __('Resultado da Compra') }}
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Algo deu errado. Por favor, tente novamente.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
