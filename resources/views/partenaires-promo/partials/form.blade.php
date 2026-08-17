@php
    $isEdit = !empty($partenaire);
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nom du partenaire</label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom', optional($partenaire)->nom) }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" class="form-control" value="{{ old('telephone', optional($partenaire)->telephone) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', optional($partenaire)->email) }}">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>Adresse</label>
            <input type="text" name="adresse" class="form-control" value="{{ old('adresse', optional($partenaire)->adresse) }}">
        </div>
    </div>

    @if($isEdit)
        <div class="col-md-4">
            <div class="form-group">
                <label>Code promo</label>
                <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', optional($codePromo)->code) }}" required>
            </div>
        </div>
    @endif

    <div class="col-md-{{ $isEdit ? '4' : '6' }}">
        <div class="form-group">
            <label>Pourcentage de réduction</label>
            <input type="number" min="1" max="100" step="0.01" name="pourcentage" class="form-control" value="{{ old('pourcentage', optional($codePromo)->pourcentage) }}" required>
        </div>
    </div>
    <div class="col-md-{{ $isEdit ? '4' : '6' }}">
        <div class="form-group">
            <label>Forfait concerné</label>
            <select name="forfait_usager_id" class="form-control">
                <option value="">Tous les forfaits</option>
                @foreach($forfait_usagers as $forfait)
                    <option value="{{ $forfait->id }}" {{ (string) old('forfait_usager_id', optional($codePromo)->forfait_usager_id) === (string) $forfait->id ? 'selected' : '' }}>
                        {{ $forfait->libelle }} - {{ number_format($forfait->prix, 0, ',', ' ') }} FCFA
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Date début</label>
            <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', optional(optional($codePromo)->date_debut)->format('Y-m-d')) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Date fin</label>
            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', optional(optional($codePromo)->date_fin)->format('Y-m-d')) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Nombre d'utilisations</label>
            <input type="number" min="1" name="usage_limit" class="form-control" value="{{ old('usage_limit', optional($codePromo)->usage_limit) }}" placeholder="Ex: 500">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Statut</label>
            <select name="statut" class="form-control">
                <option value="1" {{ (int) old('statut', optional($partenaire)->statut ?? 1) === 1 ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ (int) old('statut', optional($partenaire)->statut ?? 1) === 0 ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <label class="checkbox checkbox-primary">
            <input type="checkbox" name="is_unlimited" value="1" {{ old('is_unlimited', optional($codePromo)->is_unlimited) ? 'checked' : '' }}>
            <span>Utilisation illimitée</span>
            <span class="checkmark"></span>
        </label>
    </div>
    <div class="col-md-6">
        <label class="checkbox checkbox-primary">
            <input type="checkbox" name="one_use_per_user" value="1" {{ old('one_use_per_user', optional($codePromo)->one_use_per_user ?? true) ? 'checked' : '' }}>
            <span>Une seule utilisation par usager</span>
            <span class="checkmark"></span>
        </label>
    </div>
</div>
