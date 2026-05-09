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
                        <h4 class="card-title mb-1">Modifier un user call center</h4>
                        <p class="text-muted mb-0">Mettez a jour les informations du compte call center.</p>
                    </div>
                    <a href="{{ route('call-centers.index') }}" class="btn btn-outline-secondary">Retour</a>
                </div>

                <form action="{{ route('call-centers.update', $callCenter->id) }}" method="post">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom</label>
                                <input class="form-control" name="nom" type="text" value="{{ old('nom', $callCenter->nom) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prenoms</label>
                                <input class="form-control" name="prenoms" type="text" value="{{ old('prenoms', $callCenter->prenoms) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mobile</label>
                                <input class="form-control" name="mobile" type="text" value="{{ old('mobile', $callCenter->mobile) }}" required>
                                <small class="text-muted">Exemple: +2250700000000</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" name="email" type="email" value="{{ old('email', $callCenter->email) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nouveau mot de passe numerique</label>
                                <input class="form-control" name="password" type="password" inputmode="numeric" pattern="[0-9]*" placeholder="Laisser vide pour ne pas changer">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirmation du mot de passe</label>
                                <input class="form-control" name="password_confirmation" type="password" inputmode="numeric" pattern="[0-9]*" placeholder="Confirmer le mot de passe">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="statut" value="1" id="statut" {{ old('statut', $callCenter->statut) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statut">
                                        Compte actif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3">
                        Si vous changez le mot de passe, utilisez uniquement des chiffres.
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
