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
    public function __construct(private SmsService $smsService)
    {
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Action reservee au super admin.');
    }

    public function index()
    {
        $this->authorizeSuperAdmin();

        $data['title'] = 'Users call center';
        $data['menu'] = 'call-centers';
        $data['users'] = CallCenterUser::orderBy('id', 'desc')->get();

        return view('call-centers.index', $data);
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        $data['title'] = 'Ajouter un user call center';
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
        ]);

        $mobile = $this->formatMobile($validated['indicatif'], $validated['mobile']);

        if (CallCenterUser::where('mobile', $mobile)->exists()) {
            return back()
                ->withErrors(['mobile' => 'Ce numero de telephone est deja utilise.'])
                ->withInput();
        }

        $password = $this->generatePassword();

        $user = CallCenterUser::create([
            'nom' => html_entity_decode($validated['nom']),
            'prenoms' => html_entity_decode($validated['prenoms']),
            'mobile' => $mobile,
            'email' => html_entity_decode($validated['email']),
            'password' => Hash::make($password),
            'statut' => true,
            'created_by_super_id' => auth()->id(),
        ]);

        $smsMessage = "Bonjour {$user->prenoms},\n\n"
            . "Vos acces TOO AUTO call center :\n"
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
            session()->flash('message', 'User call center cree avec succes. Les acces ont ete envoyes par SMS.');
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
            session()->flash('message', 'User call center cree avec succes, mais le SMS des acces n\'a pas pu etre envoye.');
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

        $data['title'] = 'Modifier un user call center';
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
        ]);

        $callCenter->nom = html_entity_decode($validated['nom']);
        $callCenter->prenoms = html_entity_decode($validated['prenoms']);
        $callCenter->mobile = html_entity_decode($validated['mobile']);
        $callCenter->email = html_entity_decode($validated['email']);
        $callCenter->statut = $request->boolean('statut', $callCenter->statut);

        if (!empty($validated['password'])) {
            $callCenter->password = Hash::make($validated['password']);
        }

        $callCenter->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'User call center modifie avec succes.');

        return redirect()->route('call-centers.index');
    }

    public function destroy(CallCenterUser $callCenter)
    {
        $this->authorizeSuperAdmin();

        $callCenter->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'User call center supprime avec succes.');

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
