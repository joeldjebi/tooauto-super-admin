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
    .cc-role-picker { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .cc-role-option { cursor: pointer; display: block; margin: 0; position: relative; }
    .cc-role-option input { opacity: 0; pointer-events: none; position: absolute; }
    .cc-role-card { border: 2px solid #e5e7eb; border-radius: 12px; height: 100%; padding: 14px 16px; transition: border-color .15s, background .15s, box-shadow .15s; }
    .cc-role-option:hover .cc-role-card { border-color: #c7cbe0; }
    .cc-role-option input:checked + .cc-role-card { background: #f4f1fb; border-color: #663399; box-shadow: 0 0 0 3px rgba(102, 51, 153, .12); }
    .cc-role-option input:focus-visible + .cc-role-card { outline: 2px solid #663399; outline-offset: 2px; }
    .cc-role-title { align-items: center; display: flex; font-size: 15px; font-weight: 600; gap: 8px; justify-content: space-between; }
    .cc-role-check { border: 2px solid #c7cbe0; border-radius: 50%; display: inline-block; flex: none; height: 18px; width: 18px; }
    .cc-role-option input:checked + .cc-role-card .cc-role-check { background: #663399; border-color: #663399; box-shadow: inset 0 0 0 3px #fff; }
    .cc-role-card ul { color: #6b7280; font-size: 13px; margin: 8px 0 0; padding-left: 18px; }
</style>

<div class="form-group mb-4">
    <label class="d-block mb-2"><strong>Rôle</strong> <span class="text-muted">— définit ce que le compte peut voir dans l'espace call center</span></label>
    <div class="cc-role-picker" role="radiogroup" aria-label="Rôle">
        @foreach(\App\Models\CallCenterUser::ROLES as $roleKey => $roleLabel)
            <label class="cc-role-option">
                <input type="radio" name="role" value="{{ $roleKey }}" {{ $selectedRole === $roleKey ? 'checked' : '' }} required>
                <span class="cc-role-card">
                    <span class="cc-role-title">
                        <span><i class="{{ $roleDetails[$roleKey]['icon'] }} mr-1"></i> {{ $roleLabel }}</span>
                        <span class="cc-role-check" aria-hidden="true"></span>
                    </span>
                    <ul>
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
