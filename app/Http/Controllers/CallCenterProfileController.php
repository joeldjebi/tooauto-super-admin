<?php

namespace App\Http\Controllers;

use App\Models\CallCenterUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Profil de l'agent / superviseur connecte : informations personnelles et mot de passe.
 * Le role n'est pas modifiable ici (il est attribue par le super admin).
 */
class CallCenterProfileController extends Controller
{
    public function edit()
    {
        return view('call-centers.me.profile', [
            'title' => 'Mon profil',
            'menu' => 'call-center-profil',
            'user' => $this->agent(),
        ]);
    }

    public function update(Request $request)
    {
        $agent = $this->agent();
        // Numero stocke sans espaces ni separateurs : on compare l'unicite sur ce format.
        $request->merge(['mobile' => preg_replace('/[\s\.\-]+/', '', (string) $request->input('mobile'))]);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenoms' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9\s\.\-]+$/', Rule::unique('call_center_users', 'mobile')->ignore($agent->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('call_center_users', 'email')->ignore($agent->id)],
            'current_password' => ['required', 'string'],
        ], [
            'current_password.required' => 'Saisissez votre mot de passe actuel pour confirmer.',
            'email.unique' => 'Cet email est déjà utilisé par un autre compte.',
            'mobile.unique' => 'Ce numéro est déjà utilisé par un autre compte.',
            'mobile.regex' => 'Le numéro ne doit contenir que des chiffres (et éventuellement +, espaces, points ou tirets).',
        ], ['mobile' => 'mobile', 'email' => 'email']);

        $this->checkCurrentPassword($agent, $validated['current_password'], 'profil');

        $agent->nom = trim($validated['nom']);
        $agent->prenoms = trim($validated['prenoms']);
        $agent->mobile = $validated['mobile'];
        $agent->email = trim($validated['email']);
        $agent->save();

        return redirect()->route('call-center.profil')->with(['type' => 'alert-success', 'message' => 'Profil mis à jour.']);
    }

    public function updatePassword(Request $request)
    {
        $agent = $this->agent();

        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'string'],
            // Meme format que l'ecran de connexion du call center : 6 a 12 chiffres.
            'password' => ['required', 'digits_between:6,12', 'confirmed', 'different:current_password'],
        ], [
            'password.digits_between' => 'Le nouveau mot de passe doit contenir 6 à 12 chiffres.',
            'password.confirmed' => 'La confirmation ne correspond pas.',
            'password.different' => "Le nouveau mot de passe doit être différent de l'actuel.",
        ]);

        $this->checkCurrentPassword($agent, $validated['current_password'], 'password');

        $agent->password = Hash::make($validated['password']);
        $agent->save();

        // Nouvel identifiant de session apres un changement de mot de passe.
        $request->session()->regenerate();

        return redirect()->route('call-center.profil')->with(['type' => 'alert-success', 'message' => 'Mot de passe modifié.']);
    }

    private function agent(): CallCenterUser
    {
        return Auth::guard('call_center')->user();
    }

    private function checkCurrentPassword(CallCenterUser $agent, string $password, string $bag): void
    {
        if (! Hash::check($password, $agent->password)) {
            throw ValidationException::withMessages(['current_password' => 'Mot de passe actuel incorrect.'])->errorBag($bag === 'profil' ? 'default' : $bag);
        }
    }
}
