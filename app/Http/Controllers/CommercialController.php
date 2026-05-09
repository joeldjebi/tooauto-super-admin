<?php

namespace App\Http\Controllers;

use App\Models\Commercial;
use App\Models\Etablissement;
use App\Models\Parrain;
use App\Models\TypeEtablissement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Redirector; 
use Session;
use App\Models\Super;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CommercialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] ='Liste des commerciaux';
        $data['menu'] ='index-commercial';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data["user"])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue.');
            return back();
        }

        $data["commercials"] = Commercial::orderBy('id', 'desc')
        ->with('parrain:id,code,commercial_id')
        ->get();

        
        return view('commercials.index',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
	{
		DB::beginTransaction(); // Début de la transaction

		try {
			$request->validate([
				'nom' => 'required|string|max:100',
				'prenoms' => 'required|string',
				'mobile' => 'required|string|unique:commercials',
			]);

			$user = Auth::user();
			if (empty($user)) {
				session()->flash('type', 'alert-danger');
				session()->flash('message', "L'utilisateur est introuvable.");
				return back();
			}

			// Générer le mot de passe (ex: mobile non crypté)
			$rawPassword = strval(random_int(100000, 999999));
			$hashedPassword = Hash::make($rawPassword);

			$commercial = new Commercial();
			$commercial->nom = $request->nom;
			$commercial->prenoms = $request->prenoms;
			$commercial->mobile = '225' . $request->mobile;
			$commercial->password = $hashedPassword;
			$commercial->super_id = $user->id;
			$commercial->statut = 1;

			if (!$commercial->save()) {
				throw new \Exception("Erreur lors de la création du commercial.");
			}

			// Générer le code de parrainage
			$code = self::generateReferralCode();

			$parrain = Parrain::create([
				'commercial_id' => $commercial->id,
				'code' => $code,
			]);

			// Associer le code au commercial
			$commercial->parrain_id = $parrain->id;

			if (!$commercial->save()) {
				throw new \Exception("Erreur lors de l'association du code de parrainage.");
			}

			// Construire le message
			$message = strtoupper(
				"Votre compte a ete cree avec succes\n" .
				"Voici vos identifiants de connexion :\n" .
				"Numero de telephone : " . $commercial->mobile . "\n" .
				"Mot de passe : $rawPassword"
			);

			// Envoyer le SMS
			$this->sendSmsMtarget($message, $commercial->mobile);

			DB::commit(); // Validation de la transaction

			session()->flash('type', 'alert-success');
			session()->flash('message', "Commercial créé avec succès.");
			return back();
		} catch (\Exception $e) {
			DB::rollback(); // Annuler la transaction si une erreur se produit

			session()->flash('type', 'alert-danger');
			session()->flash('message', "Erreur lors de la création du commercial: " . $e->getMessage());
			return back();
		}
	}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'prenoms' => 'required|string',
            'mobile' => 'required|string|unique:commercials',
        ]);

        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }

        $commercial = Commercial::find($id);

        if (empty($commercial)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Commercial est introuvable.");
            return back();
        }

        $commercial->nom = $request->nom;
        $commercial->prenoms = $request->prenoms;
        $commercial->mobile = $request->mobile;

        if ($commercial->save()) {
            session()->flash('type', 'alert-success');
            session()->flash('message', "Commercial mise à jour avec succès.");
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
    
        $commercial = Commercial::find($id);
        $parrain = Parrain::where('commercial_id', $id)->first();
    
        if (empty($commercial)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "commercial introuvable.");
            return back();
        }
    
        $commercial->delete();
        if (!empty($parrain)) {
            $parrain->delete();
        }
    
        session()->flash('type', 'alert-success');
        session()->flash('message', "Commercial supprimé avec succès.");
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function activer($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }
    
        $commercial = Commercial::find($id);
    
        if (empty($commercial)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "commercial introuvable.");
            return back();
        }
    
        $commercial->statut = 1;
    
        $commercial->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Commercial activer avec succès.");
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function desactiver($id)
    {
        $data['user'] = Auth::user();
        if (empty($data['user'])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "L'utilisateur est introuvable.");
            return back();
        }
    
        $commercial = Commercial::find($id);
    
        if (empty($commercial)) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "commercial introuvable.");
            return back();
        }
    
        $commercial->statut = 0;
    
        $commercial->save();

        session()->flash('type', 'alert-success');
        session()->flash('message', "Commercial désactiver avec succès.");
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
	
	public static function generateReferralCode(): string
	{
		do {
			$code = strtoupper(Str::random(4)) . rand(1000, 9999);
		} while (Parrain::where('code', $code)->exists());

		return $code;
	}

    public function filleulsParCode(Request $request, string $code)
    {
        $data['title'] ='Filleuls';
        $data['menu'] ='index-commercial';

        $data["user"] = Super::where([
            'id' => auth()->user()->id
        ])->first();

        if (empty($data["user"])) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Une erreur est survenue.');
            return back();
        }

        $parrain = Parrain::where('code', $code)->first();
        if (!$parrain) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Code de parrainage introuvable.");
            return back();
        }

        $commercial = Commercial::find($parrain->commercial_id);

        if (!$commercial) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', "Commercial introuvable pour ce code.");
            return back();
        }

        $now = Carbon::now();
        $baseQuery = Etablissement::where('code_parrain', $code);

        $search = trim((string) $request->get('search', ''));
        $typeEtablissementId = $request->get('type_etablissement_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $filleulsQuery = Etablissement::where('code_parrain', $code)
            ->with('typeEtablissement', 'professionnel')
            ->orderBy('id', 'desc');

        if ($search !== '') {
            $filleulsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('adresse', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('typeEtablissement', function ($typeQuery) use ($search) {
                        $typeQuery->where('libelle', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('professionnel', function ($professionnelQuery) use ($search) {
                        $professionnelQuery->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('prenoms', 'like', '%' . $search . '%');
                    });
            });
        }

        if (!empty($typeEtablissementId)) {
            $filleulsQuery->where('type_etablissement_id', $typeEtablissementId);
        }

        if (!empty($dateFrom)) {
            $filleulsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $filleulsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $data['code'] = $code;
        $data['commercial'] = $commercial;
        $data['stats'] = [
            'today' => (clone $baseQuery)->whereDate('created_at', $now->toDateString())->count(),
            'this_week' => (clone $baseQuery)->whereBetween('created_at', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ])->count(),
            'current_month' => (clone $baseQuery)->whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->count(),
            'previous_month' => (clone $baseQuery)->whereYear('created_at', $now->copy()->subMonth()->year)
                ->whereMonth('created_at', $now->copy()->subMonth()->month)
                ->count(),
            'total' => (clone $baseQuery)->count(),
        ];
        $data['typeEtablissements'] = TypeEtablissement::orderBy('libelle', 'asc')->get(['id', 'libelle']);
        $data['filters'] = [
            'search' => $search,
            'type_etablissement_id' => $typeEtablissementId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
        $data['filleuls'] = $filleulsQuery->paginate(25)->withQueryString();

        return view('commercials.filleuls', $data);
    }

}
