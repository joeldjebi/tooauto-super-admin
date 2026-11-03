@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10 col-md-12">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-1">Ajouter un agent call center</h4>
                        <p class="text-muted mb-0">Les accès sont générés automatiquement et envoyés par SMS.</p>
                    </div>
                    <a href="{{ route('call-centers.index') }}" class="btn btn-outline-secondary">Retour</a>
                </div>

                <form action="{{ route('call-centers.store') }}" method="post">
                    @csrf

                    <div class="alert alert-info">
                        Renseignez les informations de l’agent call center. Le mot de passe sera créé par le système en chiffres uniquement.
                    </div>

                    @include('call-centers.partials.role-picker', ['current' => \App\Models\CallCenterUser::ROLE_AGENT])

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom</label>
                                <input class="form-control" name="nom" type="text" value="{{ old('nom') }}" placeholder="Nom" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prénoms</label>
                                <input class="form-control" name="prenoms" type="text" value="{{ old('prenoms') }}" placeholder="Prénoms" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Indicatif</label>
                                <input class="form-control" name="indicatif" type="text" value="{{ old('indicatif', '225') }}" placeholder="225" required>
                                <small class="text-muted">Sans le signe +.</small>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Mobile</label>
                                <input class="form-control" name="mobile" type="text" value="{{ old('mobile') }}" placeholder="0700000000" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" name="email" type="email" value="{{ old('email') }}" placeholder="callcenter@tooauto.com" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3">
                        Connexion dédiée call center : <strong>/call-center/login</strong>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">Créer et envoyer les accès</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
