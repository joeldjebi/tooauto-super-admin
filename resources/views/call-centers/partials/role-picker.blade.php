{{--
    Choix du rôle d'un compte call center (super admin : création et modification).
    Paramètre : $current = rôle actuel ('agent' | 'superviseur').
--}}
@php
    $selectedRole = old('role', $current ?? \App\Models\CallCenterUser::ROLE_AGENT);
    $roleDetails = [
        \App\Models\CallCenterUser::ROLE_AGENT => [
            'icon' => 'i-Headset',
            'droits' => [
                'Enregistre ses appels et ses rappels',
                'Voit « Mon activité » et « Mes rappels »',
                "Consulte le journal des appels de l'équipe",
            ],
        ],
        \App\Models\CallCenterUser::ROLE_SUPERVISEUR => [
            'icon' => 'i-Business-ManWoman',
            'droits' => [
                "Tout ce que fait un agent",
                "Accède aux rapports de toute l'équipe et à l'export CSV",
                'Voit et traite les rappels de tous les agents',
            ],
        ],
    ];
@endphp

<style>
    .cc-role-picker { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr)); }
    .cc-role-option { cursor: pointer; display: block; font-weight: 400; margin: 0; position: relative; }
    .cc-role-option input { height: 1px; opacity: 0; pointer-events: none; position: absolute; width: 1px; }
    /* La carte est un <span> (contenu autorise dans un <label>) : on la passe en bloc pour que la bordure entoure tout. */
    .cc-role-card { background: #fff; border: 1.5px solid var(--apx-border, #d2d2d7); border-radius: 14px; display: block; height: 100%; padding: 16px 18px; transition: border-color .15s, background .15s, box-shadow .15s; }
    .cc-role-option:hover .cc-role-card { border-color: var(--apx-subtle, #86868b); }
    .cc-role-option input:checked + .cc-role-card { background: rgba(0, 113, 227, .05); border-color: var(--apx-blue, #0071e3); box-shadow: 0 0 0 3px rgba(0, 113, 227, .15); }
    .cc-role-option input:focus-visible + .cc-role-card { outline: 2px solid var(--apx-blue, #0071e3); outline-offset: 2px; }
    .cc-role-title { align-items: center; display: flex; gap: 10px; }
    .cc-role-name { align-items: center; color: var(--apx-text, #1d1d1f); display: flex; flex: 1; font-size: 16px; font-weight: 600; gap: 8px; }
    .cc-role-name i { color: var(--apx-muted, #6e6e73); font-size: 18px; }
    .cc-role-option input:checked + .cc-role-card .cc-role-name i { color: var(--apx-blue, #0071e3); }
    .cc-role-check { border: 2px solid var(--apx-border, #d2d2d7); border-radius: 50%; display: block; flex: none; height: 20px; width: 20px; }
    .cc-role-option input:checked + .cc-role-card .cc-role-check { background: var(--apx-blue, #0071e3); border-color: var(--apx-blue, #0071e3); box-shadow: inset 0 0 0 4px #fff; }
    .cc-role-rights { color: var(--apx-muted, #6e6e73); display: block; font-size: 13.5px; line-height: 1.5; margin: 10px 0 0; padding-left: 18px; }
    .cc-role-rights li + li { margin-top: 2px; }
</style>

<div class="form-group mb-4">
    <div class="mb-2"><strong>Rôle</strong> <span class="text-muted">— définit ce que le compte peut voir dans l'espace call center</span></div>
    <div class="cc-role-picker" role="radiogroup" aria-label="Rôle">
        @foreach(\App\Models\CallCenterUser::ROLES as $roleKey => $roleLabel)
            <label class="cc-role-option">
                <input type="radio" name="role" value="{{ $roleKey }}" {{ $selectedRole === $roleKey ? 'checked' : '' }} required>
                <span class="cc-role-card">
                    <span class="cc-role-title">
                        <span class="cc-role-name"><i class="{{ $roleDetails[$roleKey]['icon'] }}" aria-hidden="true"></i>{{ $roleLabel }}</span>
                        <span class="cc-role-check" aria-hidden="true"></span>
                    </span>
                    <ul class="cc-role-rights">
                        @foreach($roleDetails[$roleKey]['droits'] as $droit)
                            <li>{{ $droit }}</li>
                        @endforeach
                    </ul>
                </span>
            </label>
        @endforeach
    </div>
    @error('role')<div class="text-danger mt-1">{{ $message }}</div>@enderror
</div>
