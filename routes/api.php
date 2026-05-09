<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehiculeController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\AutodocController;
use App\Http\Controllers\AnnonceConcessionnaireController;
use App\Http\Controllers\RdvConcessionnaireController;
use App\Http\Controllers\ConcessionnaireController;
use App\Http\Controllers\InfractionController;
use App\Http\Controllers\TutoController;
use App\Http\Controllers\InfosController;
use App\Http\Controllers\TvController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PaiementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// Préfixe pour API v1
Route::prefix('v1')->group(function () {

    // Routes protégées par le middleware d'authentification
    Route::middleware('auth.multiple')->group(function () {
        // Route de déconnexion
        Route::post('logout', [AuthController::class, 'logout']);
        
        // Route pour obtenir les informations de l'utilisateur
        Route::post('user-infos', [AuthController::class, 'getUser']);
        
        // Route pour mettre à jour les informations de l'utilisateur
        Route::post('/user-update/{id}', [AuthController::class, 'updateUser']);
        
        // Route pour mettre à jour le mot de passe 
        Route::post('/password-update/{id}', [AuthController::class, 'updatePassword']);
        
        // Route pour afficher la liste des etablissement
        Route::post('/index-etablissement', [EtablissementController::class, 'index']);
        
        // Route pour afficher un etablissement
        Route::post('/get-etablissement', [EtablissementController::class, 'getEtablissementById']);
		
        // Route pour afficher les types etablissements
        Route::get('/get-type-etablissement', [EtablissementController::class, 'getTypeEtablissement']);
        
        // Route pour afficher les types etablissements
        Route::get('/get-type-de_piece', [EtablissementController::class, 'getTypeDePiece']);
                
        // Route pour afficher les types etablissements
        Route::get('/get-type-de-demande', [EtablissementController::class, 'getTypeDeDemande']);
		
        // Route pour afficher les types etablissements
        Route::get('/get-type-alert', [EtablissementController::class, 'getTypeAlert']);
		
        // Route pour afficher les types etablissements
        Route::get('/get-categorie-service', [EtablissementController::class, 'getCategorieService']);
		
        // Route pour afficher les articles
        Route::get('/get-all-articles', [EtablissementController::class, 'getArticleForEtablissement']);
        
        // Route pour récupérer les articles d'un établissement spécifique
        Route::post('/get-articles-by-etablissement', [EtablissementController::class, 'getArticlesByEtablissement']);
        
        // Routes pour les promotions
        Route::get('/get-all-promotions', [PromotionController::class, 'getAllPromotions']);
        Route::post('/get-promotions-by-etablissement', [PromotionController::class, 'getPromotionsByEtablissement']);
		
        // Route pour les recherches
        Route::post('/search-all', [EtablissementController::class, 'searchAll']);
		        
        // Route pour afficher les types etablissements
        Route::post('/search-article-by-etablissement/{id}', [EtablissementController::class, 'searchArticleByEtablissement']);
        
        // Route pour afficher les types etablissements
        Route::post('/search-service-by-etablissement/{id}', [EtablissementController::class, 'searchServiceByEtablissement']);

        // Route pour afficher les type de prestation
        Route::post('/get-type-de-prestation', [EtablissementController::class, 'getTypeDePrestation']);
		
        // Route pour afficher le etablissement par type
        Route::post('/get-etablissement-by-type', [EtablissementController::class, 'getEtablissementByType']);
		
        // Route pour afficher les cabinet d'expertise
        Route::post('/get-cabinet-expertise', [EtablissementController::class, 'getCabinetExpertise']);
		
        // Route pour afficher un etablissement
        Route::post('/index-service', [ServiceController::class, 'index']);
        
        // Route pour afficher les vehicule
        Route::post('/index-vehicule', [VehiculeController::class, 'index']);
        
        // Route pour enregistrer un vehicule
        Route::post('/store-vehicule', [VehiculeController::class, 'store']);
        
        // Route pour mettre a jour un vehicule
        Route::post('/update-vehicule/{id}', [VehiculeController::class, 'update']);
        
        // Route pour supprimer un vehicule
        Route::post('/delete-vehicule', [VehiculeController::class, 'delete']);
		
		// Route pour les type de vehicule
        Route::get('/type-de-vehicule', [VehiculeController::class, 'indexTypeDeVehicule']);
        
        // Route pour les type de carburant
        Route::get('/type-de-carburant', [VehiculeController::class, 'indexTypeDeCarburant']);
        
        // Route pour la liste des marques
        Route::get('/list-marque-all', [VehiculeController::class, 'indexMarque']);
		
        // Route pour la liste des marques
        Route::post('/list-vehicule-concessionnaire-all', [VehiculeController::class, 'indexVehiculeConcessionnaire']);
        
        // Route pour la liste des vehicules par concessionnaire
        Route::post('/get-vehicule-by-concessionnaire/{id}', [VehiculeController::class, 'indexVehiculeByConcessionnaire']);
		
        // Route pour afficher les alert
        Route::post('/index-alert', [AlertController::class, 'index']);
        
        // Route pour ajouter une alert
        Route::post('/store-alert', [AlertController::class, 'store']);
        
        // Route pour mettre a jour une alert
        Route::post('/update-alert', [AlertController::class, 'update']);
        
        // Route pour delete une alert
        Route::post('/delete-alert', [AlertController::class, 'delete']);
        
        // Route pour afficher les annonces
        Route::post('/index-annonce', [AnnonceController::class, 'index']);
        
        // Route pour afficher les annonces
        Route::post('/store-annonce', [AnnonceController::class, 'store']);
        
        // Route pour mettre a jour une annonce
        Route::post('/update-annonce', [AnnonceController::class, 'update']);
        
        // Route pour mettre a jour une annonce
        Route::post('/delete-annonce', [AnnonceController::class, 'delete']);
            // Route pour delete une annonce
        Route::post('/get-all-etablissement-by-categorie-service', [EtablissementController::class, 'getEtablissementByCategorieService']);
		        
        // Route pour afficher les sous categorie de piece
        Route::post('/sous-categorie-piece', [AnnonceController::class, 'getSousCategoriePiece']);
        
        // Route pour les categorie de piece
        Route::post('/categorie-piece', [AnnonceController::class, 'getCategoriePiece']);
		        
        // Route pour delete une annonce
        Route::get('/get-all-promotion-of-etablissement', [EtablissementController::class, 'getPromotionOfEtablissement']);
        
        // Route pour delete une annonce
        Route::get('/get-all-promotion-by-etablissement/{id}', [EtablissementController::class, 'getPromotionByEtablissement']);
                        
		// Route pour afficher les etablissements par type de prestation
        Route::post('/get-type-etablissement-type-de-prestation', [EtablissementController::class, 'getEtablissementByTypeDePrestation']);

		
        // Route pour store une declaration
        Route::post('/store-declaration', [DeclarationController::class, 'storeDeclaration']);
        
        // Route pour afficher les type de declarations
        Route::post('/type-de-declaration', [DeclarationController::class, 'indexTypeDeDeclaration']);
        
        // Route pour afficher les declarations de perte
        Route::post('/declaration-de-perte', [DeclarationController::class, 'getDeclarationDePerte']);
        
        // Route pour afficher les declarations de stationnement
        Route::post('/declaration-de-stationnement', [DeclarationController::class, 'getDeclarationDeStationnement']);
		
        // Route pour update une declaration
        Route::post('/update-declaration/{id}', [DeclarationController::class, 'updateDeclaration']);
		
        // Route pour retirer les declarations de perte
        Route::post('/declaration-delete', [DeclarationController::class, 'deleteDeclaration']);
		
        // Route pour afficher les mauvais stationnement
        Route::get('/get-vehicule-abandonne', [DeclarationController::class, 'getVehiculeAbandonne']);
		
        // Route pour afficher les mauvais stationnement
        Route::get('/get-mauvais-stationnement', [DeclarationController::class, 'getMauvaisStationnement']);
		
        // Route pour save les mauvais stationnement
        Route::post('/store-mauvais-stationnement', [DeclarationController::class, 'storeMauvaisStationnement']);
		
		// Route pour afficher les autodocs
        Route::post('/get-autodoc', [AutodocController::class, 'index']);
        
        // Route pour afficher les autodocs
        Route::post('/get-type-docauto', [AutodocController::class, 'getTypeDocauto']);
		
        // Route pour store les autodocs
        Route::post('/store-autodoc', [AutodocController::class, 'store']);
        
        // Route pour update les autodocs
        Route::post('/update-autodoc/{id}', [AutodocController::class, 'update']);
        
        // Route pour delete les autodocs
        Route::post('/delete-autodoc', [AutodocController::class, 'delete']);
		
        // Route pour afficher le etablissement par type
        Route::post('/get-agent-constat', [EtablissementController::class, 'getAllCommissariatAgentConstat']);
		        
        // Route pour afficher le etablissement par type
        Route::post('/get-sapeur-pompier', [EtablissementController::class, 'getAllSapeurPompier']);
        
        // Route pour afficher les stations service normal
        Route::post('/get-station-service-normal', [EtablissementController::class, 'getAllStationServiceNormal']);
        
        // Route pour afficher les stations service electrique
        Route::post('/get-station-service-electrique', [EtablissementController::class, 'getAllStationServiceElectrique']);
        
        // Route pour afficher les alert
        Route::post('/get-alert-by-type', [AlertController::class, 'getAlertByType']);
		
        // Route pour afficher les agents constat par commune
        Route::post('/get-agent-constat-buy-commune', [EtablissementController::class, 'getAgentConstatByCommune']);
		        
        // Route pour afficher les sapeurs-pompiers pas commune
        Route::post('/get-sapeur-pompier-buy-commune', [EtablissementController::class, 'getSapeurPompierByCommune']);
		
        // Route pour afficher les sapeurs-pompiers pas commune
        Route::post('/get-communes', [EtablissementController::class, 'getCommuneAll']);
		
        // Route pour stores les constat
        Route::post('/store-constat', [AlertController::class, 'storeConstat']);
		
        // Route pour stores les incident
        Route::post('/store-incident', [AlertController::class, 'storeIncident']);
		
        // Route pour afficher les services plus
        Route::post('/get-service-plus-concessionnaire', [AnnonceConcessionnaireController::class, 'index']);

        // Route pour store les services plus
        Route::post('/store-service-plus-concessionnaire', [AnnonceConcessionnaireController::class, 'store']);

        // Route pour update les services plus
        Route::post('/update-service-plus-concessionnaire/{id}', [AnnonceConcessionnaireController::class, 'update']);

        // Route pour destroy les services plus
        Route::post('/destroy-service-plus-concessionnaire/{id}', [AnnonceConcessionnaireController::class, 'destroy']);

        // Route pour afficher les rdv
        Route::post('/get-rdv-concessionnaire', [RdvConcessionnaireController::class, 'index']);

        // Route pour store les rdv
        Route::post('/store-rdv-concessionnaire', [RdvConcessionnaireController::class, 'store']);

        // Route pour update les rdv
        Route::post('/update-rdv-concessionnaire/{id}', [RdvConcessionnaireController::class, 'update']);

        // Route pour destroy les rdv
        Route::post('/destroy-rdv-concessionnaire/{id}', [RdvConcessionnaireController::class, 'destroy']);
		
        // Route pour afficher les concessionnaires
        Route::post('/get-concessionnaire', [ConcessionnaireController::class, 'index']);
		
        // Route pour afficher les concessionnaires
        Route::post('/get-infraction', [InfractionController::class, 'index']);
		
        // Route pour afficher les tuto
        Route::post('/get-tuto', [TutoController::class, 'index']);
		
        // Route pour afficher les infis
        Route::post('/get-infos', [InfosController::class, 'index']);
		
        // Route pour afficher les tvs
        Route::post('/get-tvs', [TvController::class, 'index']);
		
		// Route pour afficher les plan d'abonnement
		Route::get('/forfaits-avantages', [PlanController::class, 'apiForfaitsAvecAvantages']);
		Route::post('/store-paiement', [PaiementController::class, 'storePaiement']);
		Route::post('/store-paiement-free', [PaiementController::class, 'storeAbonnementGratuit']);
		
		// Route pour afficher la page de vérification
		Route::post('/paiement/verifier-statut', [PaiementController::class, 'verifierStatutPaiementApi']);
    });
	

    // Routes publiques
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('login-pro', [AuthController::class, 'loginPro']);
    
    // Route pour verifier si le numero de telephone existe
    Route::post('otp-register', [AuthController::class, 'sendOtpForRegister']);
    
    // Route pour verifier si le otp est correcte
    Route::post('verify-otp-register', [AuthController::class, 'verifyOtp']);
    
    // Route pour verifier si le otp est correcte
    Route::post('send-otp-password-forget', [AuthController::class, 'sendOtpForPasswordForget']);
    
    // Route pour verifier le otp de mot de passe oublié
    Route::post('verify-otp-password-forget', [AuthController::class, 'verifyOtpPasswordForget']);
    
    // Route pour mettre a jour le mot de passe oublié
    Route::post('update-password-forget', [AuthController::class, 'passwordForgetUpdate']);

    // Route de test pour l'envoi SMS via MTarget
    Route::post('test-sms-mtarget', [AuthController::class, 'testSendSmsMtarget']);

    // Route pour les categorie par livre
    Route::post('/get-pays-all', [PaysController::class, 'indexPaysAll']);
});