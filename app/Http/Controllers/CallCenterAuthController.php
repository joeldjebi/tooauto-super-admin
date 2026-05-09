<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallCenterAuthController extends Controller
{
    public function showLogin()
    {
        return view('call-centers.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'digits_between:6,12'],
        ]);

        $credentials = [
            'email' => html_entity_decode($request->email),
            'password' => $request->password,
            'statut' => 1,
        ];

        if (Auth::guard('call_center')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            session()->flash('type', 'alert-success');
            session()->flash('message', 'Connexion call center effectuee avec succes.');

            return redirect()->route('call-center.dashboard');
        }

        session()->flash('type', 'alert-danger');
        session()->flash('message', 'Identifiants call center invalides ou compte inactif.');

        return back()->withInput($request->only('email'));
    }

    public function dashboard()
    {
        $data['title'] = 'Dashboard call center';
        $data['menu'] = 'call-center-dashboard';
        $data['user'] = Auth::guard('call_center')->user();

        return view('call-centers.dashboard', $data);
    }

    public function logout(Request $request)
    {
        Auth::guard('call_center')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('call-center.login');
    }
}
