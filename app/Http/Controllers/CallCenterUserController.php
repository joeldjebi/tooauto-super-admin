<?php

namespace App\Http\Controllers;

use App\Models\CallCenterUser;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class CallCenterUserController extends Controller
{
    private const ROLE_MESSAGES = [
        'role.required' => 'Choisissez un rôle : agent ou superviseur.',
        'role.in' => 'Le rôle choisi est invalide.',
    ];

    public function __construct(private SmsService $smsService)
    {
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Action réservée au super admin.');
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $roleFilter = in_array($request->input('role'), array_keys(CallCenterUser::ROLES), true) ? $request->input('role') : null;

        $data['title'] = 'Agents call center';
        $data['menu'] = 'call-centers';
        $data['roleFilter'] = $roleFilter;
        $data['roleCounts'] = CallCenterUser::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role')->all();
        $data['roleCounts']['total'] = array_sum($data['roleCounts']);
        $data['users'] = CallCenterUser::query()
            ->when($roleFilter, fn ($q) => $q->where('role', $roleFilter))
            ->orderBy('id', 'desc')
            ->get();

        return view('call-centers.index', $data);
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        $data['title'] = 'Ajouter un agent call center';
        $data['menu'] = 'call-centers';

        return view('call-centers.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'indicatif' => ['required', 'string', 'max:8', 'regex:/^\+?\d+$/'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9\s\.\-]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:call_center_users,email'],
            'role' => ['required', Rule::in(array_keys(CallCenterUser::ROLES))],
        ], self::ROLE_MESSAGES);

        $mobile = $this->formatMobile($validated['indicatif'], $validated['mobile']);

        if (CallCenterUser::where('mobile', $mobile)->exists()) {
            return back()
                ->withErrors(['mobile' => 'Ce numéro de téléphone est déjà utilisé.'])
                ->withInput();
        }

        $password = $this->generatePassword();

        $user = CallCenterUser::create([
            'nom' => html_entity_decode($validated['nom']),
            'prenoms' => html_entity_decode($validated['prenoms']),
            'mobile' => $mobile,
            'email' => html_entity_decode($validated['email']),
            'role' => $validated['role'],
            'password' => Hash::make($password),
            'statut' => true,
            'created_by_super_id' => auth()->id(),
        ]);

        $smsMessage = "Bonjour {$user->prenoms},\n\n"
            . "Vos accès TOO AUTO call center :\n"
            . "Profil : {$user->role_label}\n"
            . "Email : {$user->email}\n"
            . "Mot de passe : {$password}\n"
            . "Connexion : " . route('call-center.login');

        try {
            $this->smsService->sendSmsMtarget(
                $smsMessage,
                $user->mobile,
                config('services.mtarget.sender', 'TOO AUTO')
            );

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Agent call center créé avec succès. Les accès ont été envoyés par SMS.');
            session()->flash('call_center_access', [
                'email' => $user->email,
                'password' => $password,
                'login_url' => route('call-center.login'),
            ]);
        } catch (Throwable $e) {
            Log::error('Erreur lors de l\'envoi du SMS call center.', [
                'user_id' => $user->id,
                'mobile' => $user->mobile,
                'error' => $e->getMessage(),
            ]);

            session()->flash('type', 'alert-warning');
            session()->flash('message', 'Agent call center créé avec succès, mais le SMS des accès n\'a pas pu être envoyé.');
            session()->flash('call_center_access', [
                'email' => $user->email,
                'password' => $password,
                'login_url' => route('call-center.login'),
            ]);
        }

        return redirect()->route('call-centers.index');
    }

    public function edit(CallCenterUser $callCenter)
    {
        $this->authorizeSuperAdmin();

        $data['title'] = 'Modifier un agent call center';
        $data['menu'] = 'call-centers';
        $data['callCenter'] = $callCenter;

        return view('call-centers.edit', $data);
    }

    public function update(Request $request, CallCenterUser $callCenter)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('call_center_users', 'mobile')->ignore($callCenter->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('call_center_users', 'email')->ignore($callCenter->id)],
            'password' => ['nullable', 'digits_between:6,12', 'confirmed'],
            'statut' => ['nullable', 'boolean'],
            'role' => ['required', Rule::in(array_keys(CallCenterUser::ROLES))],
        ], self::ROLE_MESSAGES);

        $callCenter->nom = html_entity_decode($validated['nom']);
        $callCenter->prenoms = html_entity_decode($validated['prenoms']);
        $callCenter->mobile = html_entity_decode($validated['mobile']);
        $callCenter->email = html_entity_decode($validated['email']);
        $callCenter->statut = $request->boolean('statut', $callCenter->statut);
        $callCenter->role = $validated['role'];

        if (!empty($validated['password'])) {
            $callCenter->password = Hash::make($validated['password']);
        }

        $callCenter->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Agent call center modifié avec succès.');

        return redirect()->route('call-centers.index');
    }

    /** Changement rapide du rôle depuis la liste (Passer superviseur / Passer agent). */
    public function updateRole(Request $request, CallCenterUser $callCenter)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(CallCenterUser::ROLES))],
        ], self::ROLE_MESSAGES);

        $callCenter->role = $validated['role'];
        $callCenter->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', $callCenter->full_name . ' est maintenant ' . mb_strtolower($callCenter->role_label) . '.');

        return redirect()->back();
    }

    public function destroy(CallCenterUser $callCenter)
    {
        $this->authorizeSuperAdmin();

        $callCenter->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Agent call center supprimé avec succès.');

        return redirect()->route('call-centers.index');
    }

    private function formatMobile(string $indicatif, string $mobile): string
    {
        $indicatif = preg_replace('/\D+/', '', $indicatif);
        $mobile = preg_replace('/\D+/', '', $mobile);

        return '+' . $indicatif . $mobile;
    }

    private function generatePassword(): string
    {
        return (string) random_int(100000, 999999);
    }
}
