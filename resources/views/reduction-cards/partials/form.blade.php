@php
    $selectedType = old('discount_type', optional($card)->discount_type ?? 'percentage');
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nom de la carte</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', optional($card)->name) }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Forfait usager</label>
            <select name="forfait_usager_id" class="form-control" required>
                <option value="">Sélectionner</option>
                @foreach($forfait_usagers as $forfait)
                    <option value="{{ $forfait->id }}" {{ (string) old('forfait_usager_id', optional($card)->forfait_usager_id) === (string) $forfait->id ? 'selected' : '' }}>
                        {{ $forfait->libelle }} - {{ $forfait->duree }} mois
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Type de réduction</label>
            <select name="discount_type" class="form-control" required>
                <option value="percentage" {{ $selectedType === 'percentage' ? 'selected' : '' }}>Pourcentage</option>
                <option value="fixed" {{ $selectedType === 'fixed' ? 'selected' : '' }}>Montant fixe</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Valeur de la réduction</label>
            <input type="number" step="0.01" min="0" name="discount_value" class="form-control" value="{{ old('discount_value', optional($card)->discount_value) }}" required>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', optional($card)->description) }}</textarea>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="checkbox checkbox-primary">
                <input type="checkbox" name="statut" value="1" {{ (int) old('statut', optional($card)->statut ?? 1) === 1 ? 'checked' : '' }}>
                <span>Carte active</span>
                <span class="checkmark"></span>
            </label>
        </div>
    </div>
</div>
