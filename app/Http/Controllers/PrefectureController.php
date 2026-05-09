<?php

namespace App\Http\Controllers;

use App\Models\Prefecture;
use App\Models\Ville;
use App\Models\Super;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector; 
use Session;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrefectureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] ='Les préfectures';
        $data['menu'] ='prefectures';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data['user'])) {
            // Flash success message
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue!');
        }

        $data["prefectures"] = Prefecture::orderBy('id', 'desc')
        ->with('ville')
        ->get();

        $data["villes"] = Ville::all();
        
        return view('prefecture.index',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|unique:prefectures',
            'email' => 'required|string|unique:prefectures',
            'ville_id' => 'required|exists:villes,id',
            'adresse' => 'nullable',
        ]);

        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }
        $rawPassword = strval(random_int(100000, 999999));
        $password = Hash::make($rawPassword);

        $prefecture = new Prefecture();

        $prefecture->name = $request->name;
        $prefecture->email = $request->email;
        $prefecture->mobile = $request->mobile;
        $prefecture->password = $password;
        $prefecture->ville_id = $request->ville_id;
        $prefecture->adresse = $request->adresse;
        $prefecture->created_by = 1;

        $mobileWithIndicatif = '+225'.$request->mobile;
        $message = "Votre compte a ete cree avec succes\n" .
            "Voici vos identifiants de connexion :\n" .
            "Adresse email : $request->email\n" .
            "Mot de passe : $rawPassword";

        $this->sendSmsMtarget($message, $mobileWithIndicatif);

        if ($prefecture->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Préfecture créé avec succès.");
            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'mobile' => 'required|string|unique:prefectures,mobile,' . $id,
            'email' => 'required|string|unique:prefectures,email,' . $id,
            'ville_id' => 'required|exists:villes,id',
            'adresse' => 'nullable|string',
        ]);

        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $prefecture = Prefecture::find($id);

        if (empty($prefecture)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Préfecture est introuvable.");
            return back();
        }

        $prefecture->name = $request->name;
        $prefecture->email = $request->email;
        $prefecture->mobile = $request->mobile;
        $prefecture->ville_id = $request->ville_id;
        $prefecture->adresse = $request->adresse;

        if ($prefecture->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Préfecture mise à jour avec succès.");
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }
    
        $prefecture = Prefecture::find($id);

        if (empty($prefecture)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Préfecture est introuvable.");
            return back();
        }
    
        $prefecture->delete();
    
        session()->flash('type', 'alert-success');
        session()->flash('message', "Préfecture supprimé avec succès.");
        return back();
    }

    function sendSmsMtarget($message, $msisdn, $sender = 'TOO AUTO') {
        // URL de l'API MTarget
        $url = 'https://api-public-2.mtarget.fr/messages';
        
        // Vérifier et ajouter le signe '+' si nécessaire
        if (strpos($msisdn, '+') !== 0) {
            $msisdn = '+' . $msisdn;
        }
        
        // Paramètres d'authentification et de message
        $postData = http_build_query([
            'username' => 'bwantech',
            'password' => 'x7jyKG0IJRNH',
            'msisdn' => $msisdn,
            'msg' => $message,
            'sender' => $sender
        ]);

        // Initialisation de cURL
        $ch = curl_init();

        // Configuration des options cURL
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,  // Pour récupérer la réponse
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_SSL_VERIFYPEER => false, // Désactiver la vérification SSL pour les tests
            CURLOPT_TIMEOUT => 30, // Timeout de 30 secondes
        ]);

        // Exécution de la requête
        $response = curl_exec($ch);
        // dd($response);

        // Gestion des erreurs
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Erreur cURL : " . $error);
        }

        // Récupération du code de statut HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Fermeture de la session cURL
        curl_close($ch);

        // Vérification du code de statut HTTP
        if ($httpCode !== 200) {
            throw new \Exception("Erreur HTTP : " . $httpCode . " - Réponse : " . $response);
        }

        return $response;
    }
    
}