<?php

namespace App\Http\Controllers;

use App\Models\Super;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class AdminUserController extends Controller
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

        $data['title'] = 'Admins';
        $data['menu'] = 'admins';
        $data['admins'] = Super::where('role', 'admin')->orderBy('id', 'desc')->get();

        return view('admins.index', $data);
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        $data['title'] = 'Ajouter un admin';
        $data['menu'] = 'admins';

        return view('admins.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'indicatif' => ['required', 'string', 'max:8', 'regex:/^\+?\d+$/'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9\s\.\-]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:supers,email'],
        ]);

        $mobile = $this->formatMobile($validated['indicatif'], $validated['mobile']);

        if (Super::where('mobile', $mobile)->exists()) {
            return back()
                ->withErrors(['mobile' => 'Ce numero de telephone est deja utilise.'])
                ->withInput();
        }

        $password = $this->generatePassword();

        $admin = Super::create([
            'nom' => html_entity_decode($validated['nom']),
            'prenoms' => html_entity_decode($validated['prenoms']),
            'mobile' => $mobile,
            'email' => html_entity_decode($validated['email']),
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);

        $smsMessage = "Bonjour {$admin->prenoms},\n\n"
            . "Vos acces TOO AUTO admin :\n"
            . "Email : {$admin->email}\n"
            . "Mot de passe : {$password}\n"
            . "Connexion : " . route('login');

        try {
            $this->smsService->sendSmsMtarget(
                $smsMessage,
                $admin->mobile,
                config('services.mtarget.sender', 'TOO AUTO')
            );

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Admin cree avec succes. Les acces ont ete envoyes par SMS.');
        } catch (Throwable $e) {
            Log::error('Erreur lors de l\'envoi du SMS admin.', [
                'admin_id' => $admin->id,
                'mobile' => $admin->mobile,
                'error' => $e->getMessage(),
            ]);

            session()->flash('type', 'alert-warning');
            session()->flash('message', 'Admin cree avec succes, mais le SMS des acces n\'a pas pu etre envoye.');
        }

        return redirect()->route('admins.index');
    }

    public function edit(Super $admin)
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminUser($admin);

        $data['title'] = 'Modifier un admin';
        $data['menu'] = 'admins';
        $data['admin'] = $admin;

        return view('admins.edit', $data);
    }

    public function update(Request $request, Super $admin)
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminUser($admin);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('supers', 'mobile')->ignore($admin->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('supers', 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $admin->nom = html_entity_decode($validated['nom']);
        $admin->prenoms = html_entity_decode($validated['prenoms']);
        $admin->mobile = html_entity_decode($validated['mobile']);
        $admin->email = html_entity_decode($validated['email']);
        $admin->role = 'admin';

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Admin modifie avec succes.');

        return redirect()->route('admins.index');
    }

    public function destroy(Super $admin)
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminUser($admin);

        $admin->delete();

        session()->flash('type', 'alert-success');
        session()->flash('message', 'Admin supprime avec succes.');

        return redirect()->route('admins.index');
    }

    private function ensureAdminUser(Super $admin): void
    {
        abort_unless($admin->role === 'admin', 404);
    }

    private function formatMobile(string $indicatif, string $mobile): string
    {
        $indicatif = preg_replace('/\D+/', '', $indicatif);
        $mobile = preg_replace('/\D+/', '', $mobile);

        return '+' . $indicatif . $mobile;
    }

    private function generatePassword(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '@#$%';
        $characters = $letters . $numbers . $symbols;

        $password = [
            $letters[random_int(0, strlen($letters) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        for ($i = count($password); $i < 10; $i++) {
            $password[] = $characters[random_int(0, strlen($characters) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }
}
