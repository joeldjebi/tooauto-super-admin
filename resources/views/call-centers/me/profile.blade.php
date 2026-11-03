@extends('call-centers.layout')

@php
    $isSupervisor = $user->isSuperviseur();
    $initials = collect([$user->prenoms, $user->nom])->filter()->map(fn ($part) => mb_strtoupper(mb_substr(trim($part), 0, 1)))->implode('');
    $passwordErrors = $errors->getBag('password');
@endphp

@section('lead', 'Vos informations de connexion et votre mot de passe.')

@section('content')
    <style>
        .cc-profile-head { align-items: center; display: flex; flex-wrap: wrap; gap: 18px; }
        .cc-profile-avatar { align-items: center; background: linear-gradient(160deg, #2f8cff 0%, #0058d1 100%); border-radius: 50%; color: #fff; display: flex; flex: none; font-size: 24px; font-weight: 700; height: 64px; justify-content: center; width: 64px; }
        .cc-profile-head.is-superviseur .cc-profile-avatar { background: linear-gradient(160deg, #a35cff 0%, #6a1fd1 100%); }
        .cc-profile-name { font-size: 22px; font-weight: 700; letter-spacing: -.02em; margin: 0; }
        .cc-profile-role { align-items: center; border-radius: 999px; display: inline-flex; font-size: 13px; font-weight: 600; gap: 6px; margin-top: 6px; padding: 4px 12px; }
        .cc-profile-role.is-agent { background: var(--cc-fill); color: var(--cc-label-2); }
        .cc-profile-role.is-superviseur { background: var(--cc-blue-tint); color: var(--cc-blue); }
        .cc-profile-rights { color: var(--cc-label-2); font-size: 13px; margin: 14px 0 0; padding-left: 18px; }
        .cc-profile-rights li + li { margin-top: 2px; }
        .cc-profile-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-top: 20px; }
        .cc-profile-grid > .cc-card { margin: 0 !important; }
        .cc-profile-grid .form-group { margin-bottom: 14px; }
        .cc-profile-grid .invalid-feedback { display: block; }
    </style>

    <div class="cc-card">
        <div class="cc-profile-head {{ $isSupervisor ? 'is-superviseur' : '' }}">
            <span class="cc-profile-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
            <div>
                <h2 class="cc-profile-name">{{ $user->full_name }}</h2>
                <span class="cc-profile-role {{ $isSupervisor ? 'is-superviseur' : 'is-agent' }}">
                    <span class="cc-dot"></span>
                    {{ $user->role_label }}
                </span>
            </div>
        </div>
        <ul class="cc-profile-rights">
            @if($isSupervisor)
                <li>Vous voyez les <a href="{{ route('call-center.rapports') }}">rapports de toute l'equipe</a> et pouvez exporter les appels.</li>
                <li>Vous pouvez consulter les rappels de tous les agents.</li>
            @else
                <li>Vous enregistrez vos appels et suivez <a href="{{ route('call-center.mon-activite') }}">votre activite</a> et <a href="{{ route('call-center.mes-rappels') }}">vos rappels</a>.</li>
            @endif
            <li>Le role est attribue par le super admin : contactez-le pour le modifier.</li>
        </ul>
    </div>

    <div class="cc-profile-grid">
        <div class="cc-card">
            <div class="cc-card-header">
                <div>
                    <h5 class="cc-card-title">Informations personnelles</h5>
                    <p class="cc-card-subtitle">L'email sert d'identifiant de connexion.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('call-center.profil.update') }}" novalidate>
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label class="cc-field-label" for="p-nom">Nom</label>
                        <input id="p-nom" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $user->nom) }}" required maxlength="255" autocomplete="family-name">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6 form-group">
                        <label class="cc-field-label" for="p-prenoms">Prenoms</label>
                        <input id="p-prenoms" name="prenoms" class="form-control @error('prenoms') is-invalid @enderror" value="{{ old('prenoms', $user->prenoms) }}" required maxlength="255" autocomplete="given-name">
                        @error('prenoms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="cc-field-label" for="p-email">Email</label>
                    <input id="p-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="email">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="cc-field-label" for="p-mobile">Mobile</label>
                    <input id="p-mobile" type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $user->mobile) }}" required placeholder="+2250700000000" autocomplete="tel">
                    @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="cc-field-label" for="p-current">Mot de passe actuel</label>
                    <input id="p-current" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required inputmode="numeric" autocomplete="current-password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>

        <div class="cc-card">
            <div class="cc-card-header">
                <div>
                    <h5 class="cc-card-title">Mot de passe</h5>
                    <p class="cc-card-subtitle">6 a 12 chiffres, comme a la connexion.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('call-center.profil.password') }}" novalidate>
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="cc-field-label" for="pw-current">Mot de passe actuel</label>
                    <input id="pw-current" type="password" name="current_password" class="form-control {{ $passwordErrors->has('current_password') ? 'is-invalid' : '' }}" required inputmode="numeric" autocomplete="current-password">
                    @if($passwordErrors->has('current_password'))<div class="invalid-feedback">{{ $passwordErrors->first('current_password') }}</div>@endif
                </div>
                <div class="form-group">
                    <label class="cc-field-label" for="pw-new">Nouveau mot de passe</label>
                    <input id="pw-new" type="password" name="password" class="form-control {{ $passwordErrors->has('password') ? 'is-invalid' : '' }}" required inputmode="numeric" pattern="[0-9]{6,12}" minlength="6" maxlength="12" autocomplete="new-password">
                    @if($passwordErrors->has('password'))<div class="invalid-feedback">{{ $passwordErrors->first('password') }}</div>@endif
                </div>
                <div class="form-group">
                    <label class="cc-field-label" for="pw-confirm">Confirmer le nouveau mot de passe</label>
                    <input id="pw-confirm" type="password" name="password_confirmation" class="form-control" required inputmode="numeric" pattern="[0-9]{6,12}" minlength="6" maxlength="12" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </form>
        </div>
    </div>
@endsection
