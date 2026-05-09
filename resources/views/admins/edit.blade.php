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
                        <h4 class="card-title mb-1">Modifier un admin</h4>
                        <p class="text-muted mb-0">Mettez a jour les informations du compte admin.</p>
                    </div>
                    <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">Retour</a>
                </div>

                <form action="{{ route('admins.update', $admin->id) }}" method="post">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom</label>
                                <input class="form-control" name="nom" type="text" value="{{ old('nom', $admin->nom) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prenoms</label>
                                <input class="form-control" name="prenoms" type="text" value="{{ old('prenoms', $admin->prenoms) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mobile</label>
                                <input class="form-control" name="mobile" type="text" value="{{ old('mobile', $admin->mobile) }}" required>
                                <small class="text-muted">Exemple: +2250700000000</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input class="form-control" name="email" type="email" value="{{ old('email', $admin->email) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3">
                        Le mot de passe ne change pas depuis cette page. Creez un nouvel acces uniquement si necessaire via une procedure dediee.
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
