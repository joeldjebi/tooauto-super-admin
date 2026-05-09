@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-md-12">
        @if(session()->has('message'))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">
                <div>{{ session()->get('message') }}</div>
                @if(session()->has('call_center_access'))
                    <hr>
                    <div><strong>Email :</strong> {{ session('call_center_access.email') }}</div>
                    <div><strong>Mot de passe :</strong> {{ session('call_center_access.password') }}</div>
                    <div><strong>Connexion :</strong> {{ session('call_center_access.login_url') }}</div>
                @endif
            </div>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Users call center</h4>
                    <a href="{{ route('call-centers.create') }}" class="btn btn-primary">Ajouter un user</a>
                </div>

                @if($users->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Prenoms</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $key => $user)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $user->nom }}</td>
                                        <td>{{ $user->prenoms }}</td>
                                        <td>{{ $user->mobile }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="{{ route('call-centers.edit', $user->id) }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('call-centers.destroy', $user->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Supprimer ce user call center ?')">
                                                    <i class="nav-icon i-Close-Window font-weight-bold"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>Aucun user call center enregistre.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
