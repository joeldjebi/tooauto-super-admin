@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-lg-8 col-md-12">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="card text-left">
            <div class="card-body">
                <h4 class="card-title mb-3">Modifier le type de contrat</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Informations actuelles</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Libellé :</strong> {{ $type_contrat->libelle }}</p>
                                <p><strong>Date de création :</strong> {{ $type_contrat->created_at->format('d/m/Y H:i') }}</p>
                                <p><strong>Dernière modification :</strong> {{ $type_contrat->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Modifier les informations</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('update-type-contrat', ['id' => $type_contrat->id]) }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label for="libelle">Libellé</label>
                                        <input class="form-control" name="libelle" type="text" value="{{ old('libelle', $type_contrat->libelle) }}" placeholder="Ex: CDI, CDD, Stage, etc." required>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <button type="submit" class="btn btn-primary pd-x-20">Mettre à jour</button>
                                        <a href="{{ route('index-type-contrat') }}" class="btn btn-secondary pd-x-20 ml-2">Annuler</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
