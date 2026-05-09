<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector; 
use Session;
use App\Models\Super;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showlogin()
    {
        return view('auth.login');
    }

    /**
     * connexion des utilisateurs
     * @param Request $request
     */
    public function login(Request $request)
    {
        // Validation des entrées
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);
    
        $credentials = [
            'email' => html_entity_decode($request->email),
            'password' => $request->password,
        ];
    
        // Tentative d'authentification
        if (Auth::attempt($credentials)) {

            session()->flash('type', 'alert-success');
            session()->flash('message', "Connexion effectuée avec succès");
            return redirect()->route('dashboard');
            
        } else {
            // Si les informations d'identification sont incorrectes
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Nom d\'utilisateur ou mot de passe incorrect.');
            return back();
        }
    }    

    
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showregister()
    {
        $data['title'] ='Inscriptions';
        $data['menu'] ='register';
        
        return view('auth.register',$data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ecole  $ecole
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validation des champs
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:supers',
            'email' => 'required|string|email|max:255|unique:supers',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        // Création du super utilisateur
        $super = Super::create([
            'nom' => html_entity_decode($request->nom),
            'prenoms' => html_entity_decode($request->prenoms),
            'mobile' => html_entity_decode($request->mobile),
            'email' => html_entity_decode($request->email),
            'password' => Hash::make($request->password), // Hash du mot de passe
        ]);
    
        // Vérification si l'utilisateur a bien été créé
        if (!empty($super)) {
            // Flash success message
            session()->flash('type', 'alert-success');
            session()->flash('message', 'Votre inscription a été effectuée avec succès');
            
            return redirect('/login');
        } else {
            // Flash error message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue');
            
            return back();
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Auth $auth)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Auth $auth)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Auth $auth)
    {
        //
    }
}