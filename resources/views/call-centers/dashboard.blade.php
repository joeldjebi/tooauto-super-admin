@extends('call-centers.layout')

@section('content')
    <div class="cc-card">
        <div class="row">
            @foreach ($cards as $card)
                <div class="col-xl-4 col-md-6 mb-3">
                    <a href="{{ $card['route'] }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="mb-1 text-dark">{{ $card['label'] }}</h5>
                                <p class="text-muted mb-0">Afficher la liste</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
