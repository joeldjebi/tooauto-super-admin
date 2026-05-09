@include('layouts.header')
@include('layouts.menu')

@include('layouts.fileariane')

<div class="row">
    <div class="col-md-12">
        @if(session()->has('message'))
            <div style="padding: 10px" class="alert {{ session()->get('type') }}">{{ session()->get('message') }}</div>
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
                    <h4 class="card-title mb-0">Admins</h4>
                    <a href="{{ route('admins.create') }}" class="btn btn-primary">Ajouter un admin</a>
                </div>

                @if($admins->isNotEmpty())
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
                                @foreach($admins as $key => $admin)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $admin->nom }}</td>
                                        <td>{{ $admin->prenoms }}</td>
                                        <td>{{ $admin->mobile }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            <a class="text-success mr-2" href="{{ route('admins.edit', $admin->id) }}">
                                                <i class="nav-icon i-Pen-2 font-weight-bold"></i>
                                            </a>
                                            <form action="{{ route('admins.destroy', $admin->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger" style="background:none; border:none; cursor:pointer;" onclick="return confirm('Supprimer cet admin ?')">
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
                    <p>Aucun admin enregistre.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
