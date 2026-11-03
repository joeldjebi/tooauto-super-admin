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
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 10px;">
                    <h4 class="card-title mb-0">Agents call center</h4>
                    <a href="{{ route('call-centers.create') }}" class="btn btn-primary">Ajouter un agent</a>
                </div>

                {{-- Filtre par rôle --}}
                <div class="btn-group mb-3" role="group" aria-label="Filtrer par rôle">
                    <a href="{{ route('call-centers.index') }}" class="btn btn-sm {{ $roleFilter ? 'btn-outline-primary' : 'btn-primary' }}">Tous ({{ $roleCounts['total'] }})</a>
                    @foreach(\App\Models\CallCenterUser::ROLES as $roleKey => $roleLabel)
                        <a href="{{ route('call-centers.index', ['role' => $roleKey]) }}" class="btn btn-sm {{ $roleFilter === $roleKey ? 'btn-primary' : 'btn-outline-primary' }}">{{ $roleLabel }}s ({{ $roleCounts[$roleKey] ?? 0 }})</a>
                    @endforeach
                </div>

                @if($users->isNotEmpty())
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="language_option_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Prénoms</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
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
                                        <td data-order="{{ $user->role_label }}">
                                            <span class="badge {{ $user->isSuperviseur() ? 'badge-primary' : 'badge-secondary' }}" style="font-size: 12px; padding: 5px 10px;">
                                                <i class="{{ $user->isSuperviseur() ? 'i-Business-ManWoman' : 'i-Headset' }} mr-1"></i>{{ $user->role_label }}
                                            </span>
                                            @unless($user->statut)
                                                <span class="badge badge-light ml-1">Inactif</span>
                                            @endunless
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="callCenterActions{{ $user->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="callCenterActions{{ $user->id }}">
                                                    <a class="dropdown-item" href="{{ route('call-centers.edit', $user->id) }}">
                                                        Modifier
                                                    </a>
                                                    @php
                                                        $newRole = $user->isSuperviseur() ? \App\Models\CallCenterUser::ROLE_AGENT : \App\Models\CallCenterUser::ROLE_SUPERVISEUR;
                                                        $newRoleLabel = \App\Models\CallCenterUser::ROLES[$newRole];
                                                    @endphp
                                                    <form action="{{ route('call-centers.role', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="role" value="{{ $newRole }}">
                                                        <button type="submit" class="dropdown-item" onclick="return confirm({{ json_encode('Passer ' . trim($user->prenoms . ' ' . $user->nom) . ' en ' . mb_strtolower($newRoleLabel) . ' ?') }})">
                                                            <i class="{{ $user->isSuperviseur() ? 'i-Headset' : 'i-Business-ManWoman' }} mr-1"></i>
                                                            Passer {{ mb_strtolower($newRoleLabel) }}
                                                        </button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('call-centers.destroy', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Supprimer cet agent call center ?')">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>{{ $roleFilter ? 'Aucun compte avec ce rôle.' : 'Aucun agent call center enregistré.' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
