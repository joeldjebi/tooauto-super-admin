<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConcessionnaireController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\UsagerController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\OffresController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfessionnelController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CallCenterUserController;
use App\Http\Controllers\CallCenterAuthController;
use App\Http\Controllers\CallCenterSpaceController;

// Route::middleware(['auth', 'auth.concessionnaire'])->group(function () {
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Routes pour la gestion des admins par le super admin
    Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [AdminUserController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}/edit', [AdminUserController::class, 'edit'])->name('admins.edit');
    Route::put('/admins/{admin}', [AdminUserController::class, 'update'])->name('admins.update');
    Route::delete('/admins/{admin}', [AdminUserController::class, 'destroy'])->name('admins.destroy');

    // Routes pour la gestion des users call center par le super admin
    Route::get('/call-centers', [CallCenterUserController::class, 'index'])->name('call-centers.index');
    Route::get('/call-centers/create', [CallCenterUserController::class, 'create'])->name('call-centers.create');
    Route::post('/call-centers', [CallCenterUserController::class, 'store'])->name('call-centers.store');
    Route::get('/call-centers/{callCenter}/edit', [CallCenterUserController::class, 'edit'])->name('call-centers.edit');
    Route::put('/call-centers/{callCenter}', [CallCenterUserController::class, 'update'])->name('call-centers.update');
    Route::delete('/call-centers/{callCenter}', [CallCenterUserController::class, 'destroy'])->name('call-centers.destroy');

    // Routes pour l'enregistrement de l'établissement
    Route::get('/concessionnaire/create', [ConcessionnaireController::class, 'create'])->name('concessionnaire.create');
    Route::post('/concessionnaire/store', [ConcessionnaireController::class, 'storeConcessionnaire'])->name('concessionnaire.store');
    Route::get('/concessionnaire/edit', [ConcessionnaireController::class, 'editConcessionnaire'])->name('concessionnaire.edit');
    Route::post('/concessionnaire/update/{id}', [ConcessionnaireController::class, 'updateConcessionnaire'])->name('concessionnaire.update');
    Route::delete('/concessionnaires/{id}', [ConcessionnaireController::class, 'destroyConcessionnaire'])->name('concessionnaires.destroy');

    Route::get('/vehicule', [ConcessionnaireController::class, 'indexVehicule'])->name('index-vehicule');
    Route::post('/vehicule-store', [ConcessionnaireController::class, 'storeVehicule'])->name('vehicule.store');
    Route::post('/vehicule-update/{id}', [ConcessionnaireController::class, 'updateVehicule'])->name('vehicule.update');
    Route::delete('/vehicule-destroy/{id}', [ConcessionnaireController::class, 'destroyVehicule'])->name('vehicule.destroy');

    Route::get('/annonce', [ConcessionnaireController::class, 'indexAnnonce'])->name('index-annonce');
    Route::get('/alerte', [DashboardController::class, 'getAllAlert'])->name('index-alerte');
    Route::get('/rdv', [ConcessionnaireController::class, 'indexRdvAnnonce'])->name('rdv.index');
    Route::post('/store-rdv/{id}', [ConcessionnaireController::class, 'storeRdvAnnonce'])->name('rdv.store');
    Route::get('/flotte', [ConcessionnaireController::class, 'indexGestionnaireDeFlotte'])->name('flotte');
    Route::post('/store-flotte', [ConcessionnaireController::class, 'storeOffre'])->name('store.flotte');

    Route::get('/profil', [DashboardController::class, 'profil'])->name('profil.index');
    Route::post('/profil/update', [DashboardController::class, 'updateProfil'])->name('profil.update');
    Route::post('/password/update', [DashboardController::class, 'updatepassword'])->name('password.update');

    // Routes pour les usagers
    Route::get('/usagers', [UsagerController::class, 'indexUsager'])->name('index-usagers');
    Route::post('/usagers/{id}/change-forfait', [UsagerController::class, 'changeForfaitUsager'])->name('usager.change-forfait');
    Route::get('/usagers/{id}', [UsagerController::class, 'showUsager'])->name('usager.show');
    Route::post('/store-usagers', [UsagerController::class, 'storeUsager'])->name('store-usagers');
    Route::post('/update-usagers/{id}', [UsagerController::class, 'updateUsager'])->name('update-usagers');
    Route::delete('/delete-usagers/{id}', [UsagerController::class, 'destroyUsager'])->name('delete-usagers');
    Route::post('/usager/toggle-status', [DashboardController::class, 'toggleStatusUsager'])->name('usager.toggleStatus');

    // Routes pour les établissements
    Route::get('/etablissements', [EtablissementController::class, 'indexEtablissement'])->name('index-etablissements');
    Route::post('/etablissements/{id}/update', [EtablissementController::class, 'updateEtablissement'])->name('update-etablissement');
    Route::delete('/etablissements/{id}', [EtablissementController::class, 'destroyEtablissement'])->name('delete-etablissement');
    Route::get('/etablissements/{id}', [EtablissementController::class, 'showEtablissement'])->name('show-etablissement');
    Route::get('/etablissements/{id}/articles', [EtablissementController::class, 'showEtablissementArticles'])->name('show-etablissement-articles');
    Route::get('/etablissements/{id}/annonces', [EtablissementController::class, 'showEtablissementAnnonces'])->name('show-etablissement-annonces');

    // Routes pour les services
    Route::get('/services', [ServiceController::class, 'indexService'])->name('index-services');
    Route::get('/professionnels', [ProfessionnelController::class, 'index'])->name('index-professionnels');
    Route::post('/professionnels', [ProfessionnelController::class, 'store'])->name('professionnels.store');
    Route::post('/professionnels/{id}/update', [ProfessionnelController::class, 'update'])->name('professionnels.update');
    Route::delete('/professionnels/{id}', [ProfessionnelController::class, 'destroy'])->name('professionnels.destroy');
    Route::post('/professionnels/{id}/activer', [ProfessionnelController::class, 'activer'])->name('professionnels.activer');
    Route::post('/professionnels/{id}/desactiver', [ProfessionnelController::class, 'desactiver'])->name('professionnels.desactiver');

    // Routes pour les lavages de véhicules
    Route::get('/prestataire-lavages', [DashboardController::class, 'indexPrestataireLavage'])->name('prestataire-lavages.index');
    Route::post('/prestataire-lavages', [DashboardController::class, 'storePrestataireLavage'])->name('prestataire-lavages.store');
    Route::post('/prestataire-lavages/{id}', [DashboardController::class, 'updatePrestataireLavage'])->name('prestataire-lavages.update');
    Route::delete('/prestataire-lavages/{id}', [DashboardController::class, 'destroyPrestataireLavage'])->name('prestataire-lavages.destroy');
    Route::get('/demande-lavages', [DashboardController::class, 'indexDemandeLavage'])->name('demande-lavages.index');
    Route::post('/demande-lavages/{id}', [DashboardController::class, 'updateDemandeLavage'])->name('demande-lavages.update');

    // Routes pour les articles
    Route::get('/articles', [DashboardController::class, 'getAllArticle'])->name('index-article');

    // Routes pour les abonnements
    Route::get('/abonnement-pro', [DashboardController::class, 'getAbonnementPro'])->name('abonnement-pro');
    Route::get('/abonnement-usager', [DashboardController::class, 'getAbonnementUsager'])->name('abonnement-usager');

    // Routes pour les concessionnaires
    Route::get('/concessionnaires-liste', [DashboardController::class, 'getConcessionnairesListe'])->name('concessionnaires.liste');
    Route::get('/concessionnaires-details/{id}', [DashboardController::class, 'getConcessionnaireDetails'])->name('concessionnaires.details');

    // Routes pour les parrainages commerciaux
    Route::get('/parrainages-commerciaux', [DashboardController::class, 'getParrainageForCommerciaux'])->name('parrainage-commercial');

    // Routes pour les services publics
    Route::get('/prefectures', [DashboardController::class, 'getPrefecture'])->name('index-prefecture');
    Route::post('/store-prefecture', [DashboardController::class, 'storePrefecture'])->name('store-prefecture');
    Route::post('/update-prefecture/{id}', [DashboardController::class, 'updatePrefecture'])->name('update-prefecture');
    Route::delete('/destroy-prefecture/{id}', [DashboardController::class, 'destroyPrefecture'])->name('destroy-prefecture');
    Route::get('/sapeur-pompier', [DashboardController::class, 'indexSapeurPompier'])->name('index-sapeur_pompier');
    Route::get('/incidents-sapeur-pompier/{id}', [DashboardController::class, 'getIncidentsSapeurPompier'])->name('incidents-sapeur_pompier');
    Route::post('/store-sapeur-pompier', [DashboardController::class, 'storeSapeurPompier'])->name('store-sapeur_pompier');
    Route::post('/update-sapeur-pompier/{id}', [DashboardController::class, 'updateSapeurPompier'])->name('update-sapeur_pompier');
    Route::delete('/destroy-sapeur-pompier/{id}', [DashboardController::class, 'destroySapeurPompier'])->name('destroy-sapeur_pompier');

    // Routes pour les offres d'emploi
    Route::get('/offres-emploi', [OffresController::class, 'indexOffres'])->name('index-offre');
    Route::get('/offres-emploi/create', [OffresController::class, 'createOffres'])->name('create-offre');
    Route::post('/offres-emploi/store', [OffresController::class, 'storeOffreEmploi'])->name('store-offre');
    Route::get('/offres-emploi/edit/{id}', [OffresController::class, 'editOffreEmploi'])->name('edit-offre');
    Route::post('/offres-emploi/update/{id}', [OffresController::class, 'updateOffreEmploi'])->name('update-offre');
    Route::delete('/offres-emploi/destroy/{id}', [OffresController::class, 'destroyOffreEmploi'])->name('delete-offre');

    // Routes pour les offres d'emploi détaillées
    Route::get('/offres-emploi/create-detail', [OffresController::class, 'createOffreEmploiDetail'])->name('create-offre-detail');
    Route::post('/offres-emploi/store-detail', [OffresController::class, 'storeOffreEmploiDetail'])->name('store-offre-detail');
    Route::get('/offres-emploi-recrutement', [OffresController::class, 'showOffres'])->name('show-offres');
    Route::get('/offres-emploi/edit-recrutement/{id}', [OffresController::class, 'editOffreEmploiRecrutement'])->name('edit-offre-recrutement');
    Route::post('/offres-emploi/update-recrutement/{id}', [OffresController::class, 'updateOffreEmploiRecrutement'])->name('update-offre-recrutement');
    Route::delete('/offres-emploi/delete-recrutement/{id}', [OffresController::class, 'destroyOffreEmploiRecrutement'])->name('delete-offre-recrutement');

    // Routes pour les candidats recrutement
    Route::get('/candidats-recrutement', [OffresController::class, 'indexCandidatsRecrutement'])->name('index-candidats-recrutement');
    Route::get('/candidats-recrutement/show/{id}', [OffresController::class, 'showCandidatRecrutement'])->name('show-candidat-recrutement');
    Route::delete('/candidats-recrutement/delete/{id}', [OffresController::class, 'destroyCandidatRecrutement'])->name('delete-candidat-recrutement');


    Route::get('/types-offres', [OffresController::class, 'indexTypeOffre'])->name('index-type-offre');
    Route::get('/edit-type-offre/{id}', [OffresController::class, 'editTypeOffre'])->name('edit-type-offre');
    Route::post('/store-type-offre', [OffresController::class, 'storeTypeOffre'])->name('store-type-offre');
    Route::post('/update-type-offre/{id}', [OffresController::class, 'updateTypeOffre'])->name('update-type-offre');
    Route::delete('/delete-type-offre/{id}', [OffresController::class, 'destroyTypeOffre'])->name('delete-type-offre');
    Route::get('/types-contrats', [OffresController::class, 'indexTypeContrat'])->name('index-type-contrat');
    Route::get('/edit-type-contrat/{id}', [OffresController::class, 'editTypeContrat'])->name('edit-type-contrat');
    Route::post('/store-type-contrat', [OffresController::class, 'storeTypeContrat'])->name('store-type-contrat');
    Route::post('/update-type-contrat/{id}', [OffresController::class, 'updateTypeContrat'])->name('update-type-contrat');
    Route::delete('/delete-type-contrat/{id}', [OffresController::class, 'destroyTypeContrat'])->name('delete-type-contrat');
    Route::get('/candidats', [OffresController::class, 'indexCandidat'])->name('index-candidat');
    Route::get('/show-candidat/{id}', [OffresController::class, 'showCandidat'])->name('show-candidat');
    Route::delete('/delete-candidat/{id}', [OffresController::class, 'destroyCandidat'])->name('delete-candidat');

    // Routes pour les données de base
    Route::get('/categories-services', [DashboardController::class, 'getCategorieService'])->name('index-service-categorie');
    Route::get('/sous-categories-services', [DashboardController::class, 'getSousCategorieService'])->name('index-sous-categorie-service');
    Route::get('/ss-categories-services', [DashboardController::class, 'getSsCategorieService'])->name('index-ss-categorie-service');
    Route::post('/store-service-categorie', [DashboardController::class, 'storeCategorieService'])->name('store-service-categorie');
    Route::post('/update-service-categorie/{id}', [DashboardController::class, 'updateCategorieService'])->name('update-service-categorie');
    Route::delete('/delete-service-categorie/{id}', [DashboardController::class, 'destroyCategorieService'])->name('delete-service-categorie');
    Route::post('/store-sous-categorie-service', [DashboardController::class, 'storeSousCategorieService'])->name('store-sous-categorie-service');
    Route::post('/update-sous-categorie-service/{id}', [DashboardController::class, 'updateSousCategorieService'])->name('update-sous-categorie-service');
    Route::delete('/delete-sous-categorie-service/{id}', [DashboardController::class, 'destroySousCategorieService'])->name('delete-sous-categorie-service');
    Route::post('/store-ss-categorie-service', [DashboardController::class, 'storeSsCategorieService'])->name('store-ss-categorie-service');
    Route::post('/update-ss-categorie-service/{id}', [DashboardController::class, 'updateSsCategorieService'])->name('update-ss-categorie-service');
    Route::delete('/delete-ss-categorie-service/{id}', [DashboardController::class, 'destroySsCategorieService'])->name('delete-ss-categorie-service');
    Route::get('/types-etablissements', [DashboardController::class, 'getTypeEtablissement'])->name('index-type-etablissement');
    Route::post('/store-type-etablissement', [DashboardController::class, 'storeTypeEtablissement'])->name('store-type-etablissement');
    Route::post('/update-type-etablissement/{id}', [DashboardController::class, 'updateTypeEtablissement'])->name('update-type-etablissement');
    Route::delete('/delete-type-etablissement/{id}', [DashboardController::class, 'destroyTypeEtablissement'])->name('delete-type-etablissement');
    Route::get('/forfaits-pro', [DashboardController::class, 'getGetForfaitPro'])->name('index-forfait-pro');
    Route::post('/store-forfait-pro', [DashboardController::class, 'storeGetForfaitPro'])->name('store-forfait-pro');
    Route::post('/update-forfait-pro/{id}', [DashboardController::class, 'updateGetForfaitPro'])->name('update-forfait-pro');
    Route::delete('/delete-forfait-pro/{id}', [DashboardController::class, 'destroyForfaitPro'])->name('delete-forfait-pro');
	Route::get('/forfaits-usagers', [DashboardController::class, 'indexForfaitUsager'])->name('index-forfait-usager');
    Route::post('/store-forfait-usager', [DashboardController::class, 'storeForfaitUsager'])->name('store-forfait-usager');
    Route::post('/update-forfait-usager/{id}', [DashboardController::class, 'updateForfaitUsager'])->name('update-forfait-usager');
    Route::delete('/delete-forfait-usager/{id}', [DashboardController::class, 'destroyForfaitUsager'])->name('delete-forfait-usager');
    Route::get('/forfait-avantage-usagers', [DashboardController::class, 'indexForfaitAvantageUsager'])->name('index-forfait-avantage-usager');
    Route::post('/store-forfait-avantage-usager', [DashboardController::class, 'storeForfaitAvantageUsager'])->name('store-forfait-avantage-usager');
    Route::post('/update-forfait-avantage-usager/{id}', [DashboardController::class, 'updateForfaitAvantageUsager'])->name('update-forfait-avantage-usager');
    Route::delete('/delete-forfait-avantage-usager/{id}', [DashboardController::class, 'destroyForfaitAvantageUsager'])->name('delete-forfait-avantage-usager');
    Route::get('/marques', [DashboardController::class, 'getMarque'])->name('index-marque');
    Route::post('/store-marque', [DashboardController::class, 'storeMarque'])->name('store-marque');
    Route::post('/update-marque/{id}', [DashboardController::class, 'updateMarque'])->name('update-marque');
    Route::delete('/delete-marque/{id}', [DashboardController::class, 'destroyMarque'])->name('delete-marque');
    Route::get('/pays', [DashboardController::class, 'getPays'])->name('index-pays');
    Route::post('/store-pays', [DashboardController::class, 'storePays'])->name('store-pays');
    Route::post('/update-pays/{id}', [DashboardController::class, 'updatePays'])->name('update-pays');
    Route::delete('/delete-pays/{id}', [DashboardController::class, 'destroyPays'])->name('delete-pays');
    Route::get('/villes', [DashboardController::class, 'getVille'])->name('index-ville');
    Route::post('/store-ville', [DashboardController::class, 'storeVille'])->name('store-ville');
    Route::post('/update-ville/{id}', [DashboardController::class, 'updateVille'])->name('update-ville');
    Route::delete('/delete-ville/{id}', [DashboardController::class, 'destroyVille'])->name('delete-ville');
    Route::get('/communes', [DashboardController::class, 'getCommune'])->name('index-commune');
    Route::post('/store-commune', [DashboardController::class, 'storeCommune'])->name('store-commune');
    Route::post('/update-commune/{id}', [DashboardController::class, 'updateCommune'])->name('update-commune');
    Route::delete('/delete-commune/{id}', [DashboardController::class, 'destroyCommune'])->name('delete-commune');
    Route::get('/types-alerts', [DashboardController::class, 'getTypeAlert'])->name('index-type-alert');
    Route::post('/store-type-alert', [DashboardController::class, 'storeTypeAlert'])->name('store-type-alert');
    Route::post('/update-type-alert/{id}', [DashboardController::class, 'updateTypeAlert'])->name('update-type-alert');
    Route::delete('/delete-type-alert/{id}', [DashboardController::class, 'destroyTypeAlert'])->name('delete-type-alert');
    Route::get('/types-carburants', [DashboardController::class, 'getTypeDeCarburant'])->name('index-type-de-carburant');
    Route::post('/store-type-de-carburant', [DashboardController::class, 'storeTypeDeCarburant'])->name('store-type-de-carburant');
    Route::post('/update-type-de-carburant/{id}', [DashboardController::class, 'updateTypeDeCarburant'])->name('update-type-de-carburant');
    Route::delete('/delete-type-de-carburant/{id}', [DashboardController::class, 'destroyTypeDeCarburant'])->name('delete-type-de-carburant');
    Route::get('/types-pieces', [DashboardController::class, 'getTypeDePiece'])->name('index-type-de-piece');
    Route::post('/store-type-de-piece', [DashboardController::class, 'storeTypeDePiece'])->name('store-type-de-piece');
    Route::post('/update-type-de-piece/{id}', [DashboardController::class, 'updateTypeDePiece'])->name('update-type-de-piece');
    Route::delete('/delete-type-de-piece/{id}', [DashboardController::class, 'destroyTypeDePiece'])->name('delete-type-de-piece');
    Route::get('/types-vehicules', [DashboardController::class, 'getTypeDeVehicule'])->name('index-type-de-vehicule');
    Route::post('/store-type-de-vehicule', [DashboardController::class, 'storeTypeDeVehicule'])->name('store-type-de-vehicule');
    Route::post('/update-type-de-vehicule/{id}', [DashboardController::class, 'updateTypeDeVehicule'])->name('update-type-de-vehicule');
    Route::delete('/delete-type-de-vehicule/{id}', [DashboardController::class, 'destroyTypeDeVehicule'])->name('delete-type-de-vehicule');
    Route::get('/types-declarations', [DashboardController::class, 'getTypeDeDeclaration'])->name('index-type-de-declaration');
    Route::post('/store-type-de-declaration', [DashboardController::class, 'storeTypeDeDeclaration'])->name('store-type-de-declaration');
    Route::post('/update-type-de-declaration/{id}', [DashboardController::class, 'updateTypeDeDeclaration'])->name('update-type-de-declaration');
    Route::delete('/delete-type-de-declaration/{id}', [DashboardController::class, 'destroyTypeDeDeclaration'])->name('delete-type-de-declaration');
    Route::get('/types-prestations', [DashboardController::class, 'getTypeDePrestation'])->name('index-type-de-prestation');
    Route::post('/store-type-de-prestation', [DashboardController::class, 'storeTypeDePrestation'])->name('store-type-de-prestation');
    Route::post('/update-type-de-prestation/{id}', [DashboardController::class, 'updateTypeDePrestation'])->name('update-type-de-prestation');
    Route::delete('/delete-type-de-prestation/{id}', [DashboardController::class, 'destroyTypeDePrestation'])->name('delete-type-de-prestation');
    Route::get('/cabinets-expertise', [DashboardController::class, 'indexCabinetExpertise'])->name('index-cabinet-expertise');
    Route::get('/visites-techniques', [DashboardController::class, 'getVisiteTechnique'])->name('index-visite-technique');
    Route::post('/visite-technique-update/{id}', [DashboardController::class, 'updateVisiteTechnique'])->name('visite_technique.update');
    Route::get('/revisions-techniques', [DashboardController::class, 'getRevisionTechnique'])->name('index-revision-technique');
    Route::post('/revision-technique-update/{id}', [DashboardController::class, 'updateRevisionTechnique'])->name('revision_technique.update');
    Route::get('/station-services', [DashboardController::class, 'indexStationService'])->name('index-station_service');
    Route::post('/store-station-service', [DashboardController::class, 'storeStationService'])->name('store-station_service');
    Route::post('/update-station-service/{id}', [DashboardController::class, 'updateStationService'])->name('update-station_service');
    Route::delete('/delete-station-service/{id}', [DashboardController::class, 'destroyStationService'])->name('destroy-station_service');
    Route::get('/categories-tv', [DashboardController::class, 'getCategorieTv'])->name('index-categorie-tv');
    Route::get('/tv', [DashboardController::class, 'getTv'])->name('index-tv');
    Route::get('/informations', [DashboardController::class, 'getInfo'])->name('index-info');
    Route::get('/tutos', [DashboardController::class, 'indexTuto'])->name('index-tuto');
    Route::post('/store-tuto', [DashboardController::class, 'storeTuto'])->name('store-tuto');
    Route::post('/update-tuto/{id}', [DashboardController::class, 'updateTuto'])->name('update-tuto');
    Route::delete('/delete-tuto/{id}', [DashboardController::class, 'destroyTuto'])->name('delete-tuto');
    Route::post('/store-categorie-tuto', [DashboardController::class, 'storeCategorieTuto'])->name('store-categorie-tuto');
    Route::post('/update-categorie-tuto/{id}', [DashboardController::class, 'updateCategorieTuto'])->name('update-categorie-tuto');
    Route::delete('/delete-categorie-tuto/{id}', [DashboardController::class, 'destroyCategorieTuto'])->name('delete-categorie-tuto');
    Route::post('/store-info', [DashboardController::class, 'storeInfo'])->name('store-info');
    Route::post('/update-info/{id}', [DashboardController::class, 'updateInfo'])->name('update-info');
    Route::delete('/delete-info/{id}', [DashboardController::class, 'destroyInfo'])->name('delete-info');

    // Routes pour les contacts utils
    Route::get('/contacts-utils', [DashboardController::class, 'getContactUtil'])->name('index-contact-util');
    Route::post('/store-contact-util', [DashboardController::class, 'storeContactUtil'])->name('store-contact-util');
    Route::post('/update-contact-util/{id}', [DashboardController::class, 'updateContactUtil'])->name('update-contact-util');
    Route::delete('/delete-contact-util/{id}', [DashboardController::class, 'destroyContactUtil'])->name('delete-contact-util');

	// Routes pour les commissariat infos
    Route::get('/commissariat-inofs', [DashboardController::class, 'getCommissariatInofs'])->name('index-commissariat-inofs');
    Route::post('/store-commissariat-inofs', [DashboardController::class, 'storeCommissariatInofs'])->name('store-commissariat-inofs');
    Route::post('/update-commissariat-inofs/{id}', [DashboardController::class, 'updateCommissariatInofs'])->name('update-commissariat-inofs');
    Route::delete('/delete-commissariat-inofs/{id}', [DashboardController::class, 'destroyCommissariatInofs'])->name('delete-commissariat-inofs');


    // Routes pour les couleurs
    Route::get('/couleurs', [DashboardController::class, 'getCouleur'])->name('index-couleur');
    Route::post('/store-couleur', [DashboardController::class, 'storeCouleur'])->name('store-couleur');
    Route::post('/update-couleur/{id}', [DashboardController::class, 'updateCouleur'])->name('update-couleur');
    Route::delete('/delete-couleur/{id}', [DashboardController::class, 'destroyCouleur'])->name('delete-couleur');

	// Routes pour les entreprises d'assurance
    Route::get('/entreprises-assurances', [DashboardController::class, 'getEntreprisesAssurances'])->name('index-entreprises-assurances');
    Route::post('/store-entreprise-assurance', [DashboardController::class, 'storeEntrepriseAssurance'])->name('store-entreprise-assurance');
    Route::post('/update-entreprise-assurance/{id}', [DashboardController::class, 'updateEntrepriseAssurance'])->name('update-entreprise-assurance');
    Route::delete('/delete-entreprise-assurance/{id}', [DashboardController::class, 'destroyEntrepriseAssurance'])->name('delete-entreprise-assurance');

    // Routes pour les acteurs
    Route::get('/acteurs', [DashboardController::class, 'getActeurs'])->name('index-acteurs');
    Route::post('/store-acteur', [DashboardController::class, 'storeActeur'])->name('store-acteur');
    Route::post('/update-acteur/{id}', [DashboardController::class, 'updateActeur'])->name('update-acteur');
    Route::delete('/delete-acteur/{id}', [DashboardController::class, 'destroyActeur'])->name('delete-acteur');

    // Routes pour les QR codes générés
    Route::get('/qrcode-generates', [DashboardController::class, 'getQrcodeGenerate'])->name('index-qrcode-generate');
    Route::post('/store-qrcode-generate', [DashboardController::class, 'storeQrcodeGenerate'])->name('store-qrcode-generate');
    Route::post('/update-qrcode-generate/{id}', [DashboardController::class, 'updateQrcodeGenerate'])->name('update-qrcode-generate');
    Route::delete('/delete-qrcode-generate/{id}', [DashboardController::class, 'destroyQrcodeGenerate'])->name('delete-qrcode-generate');

    // Routes pour les TV
    Route::post('/store-tv', [DashboardController::class, 'storeTv'])->name('store-tv');
    Route::post('/update-tv/{id}', [DashboardController::class, 'updateTv'])->name('update-tv');
    Route::delete('/delete-tv/{id}', [DashboardController::class, 'destroyTv'])->name('delete-tv');

    // Routes pour les catégories TV
    Route::post('/store-categorie-tv', [DashboardController::class, 'storeCategorieTv'])->name('store-categorie-tv');
    Route::post('/update-categorie-tv/{id}', [DashboardController::class, 'updateCategorieTv'])->name('update-categorie-tv');
    Route::delete('/delete-categorie-tv/{id}', [DashboardController::class, 'destroyCategorieTv'])->name('delete-categorie-tv');

    // Routes pour les cabinets d'expertise
    Route::get('/create-cabinet-expertise', [DashboardController::class, 'createCabinetExpertise'])->name('create-cabinet-expertise');
    Route::post('/store-cabinet-expertise', [DashboardController::class, 'storeCabinetExpertise'])->name('cabinet-expertise.store');
    Route::get('/edit-cabinet-expertise/{id}', [DashboardController::class, 'editCabinetExpertise'])->name('edit-cabinet-expertise');
    Route::post('/update-cabinet-expertise/{id}', [DashboardController::class, 'updateCabinetExpertise'])->name('update-cabinet-expertise');
    Route::delete('/delete-cabinet-expertise/{id}', [DashboardController::class, 'destroyCabinetExpertise'])->name('delete-cabinet-expertise');
    Route::post('/toggle-status-cabinet-expertise/{id}', [DashboardController::class, 'toggleStatusCabinetExpertise'])->name('toggle-status-cabinet-expertise');

    // Routes pour les visites techniques
    Route::post('/store-visite-technique', [DashboardController::class, 'storeVisiteTechnique'])->name('visite_technique.store');
    Route::get('/visite-technique-edit/{id}', [DashboardController::class, 'editVisiteTechnique'])->name('visite_technique.edit');
    Route::post('/visite-technique-update/{id}', [DashboardController::class, 'updateVisiteTechnique'])->name('visite_technique.update');
    Route::post('/toggle-visite-technique/{id}', [DashboardController::class, 'toggleStatusVisiteTechnique'])->name('visite_technique.toggle');
    Route::delete('/delete-visite-technique/{id}', [DashboardController::class, 'destroyVisiteTechnique'])->name('visite_technique.destroy');

    // Routes pour les révisions techniques
    Route::post('/store-revision-technique', [DashboardController::class, 'storeRevisionTechnique'])->name('revision_technique.store');
    Route::get('/revision-technique-edit/{id}', [DashboardController::class, 'editRevisionTechnique'])->name('revision_technique.edit');
    Route::post('/revision-technique-update/{id}', [DashboardController::class, 'updateRevisionTechnique'])->name('revision_technique.update');
    Route::post('/toggle-revision-technique/{id}', [DashboardController::class, 'toggleStatusRevisionTechnique'])->name('revision_technique.toggle');
    Route::delete('/delete-revision-technique/{id}', [DashboardController::class, 'destroyRevisionTechnique'])->name('revision_technique.destroy');

    Route::post('/etablissement.toggleStatus', [EtablissementController::class, 'toggleStatusEtablissement'])->name('etablissement.toggleStatus');

    // Routes pour les promotions
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
    Route::get('/promotions/{id}', [PromotionController::class, 'show'])->name('promotions.show');
    Route::get('/promotions/{id}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
    Route::put('/promotions/{id}', [PromotionController::class, 'update'])->name('promotions.update');
    Route::delete('/promotions/{id}', [PromotionController::class, 'destroy'])->name('promotions.destroy');
    Route::post('/promotions/{id}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggle-status');

    Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');

    // Routes pour les commerciaux
    Route::get('/commerciaux', [CommercialController::class, 'index'])->name('index-commercial');
    Route::post('/store-commercial', [CommercialController::class, 'store'])->name('commercial.store');
    Route::post('/update-commercial/{id}', [CommercialController::class, 'update'])->name('commercial.update');
    Route::delete('/delete-commercial/{id}', [CommercialController::class, 'destroy'])->name('commercial.destroy');
    Route::post('/commercial/{id}/activer', [CommercialController::class, 'activer'])->name('commercial.activer');
    Route::post('/commercial/{id}/desactiver', [CommercialController::class, 'desactiver'])->name('commercial.desactiver');
    
    Route::get('/commerciaux/{code}/filleuls', [CommercialController::class, 'filleulsParCode'])->name('commercial.filleuls');
    Route::post('/commercial-wallet/commission-setting', [CommercialController::class, 'updateCommissionSetting'])->name('commercial.wallet.commission-setting');
    Route::post('/commercial-wallet/{id}/payout', [CommercialController::class, 'payout'])->name('commercial.wallet.payout');
    Route::get('/commercial-wallet/{id}/historique', [CommercialController::class, 'walletHistory'])->name('commercial.wallet.history');

    // Routes pour les notifications Firebase (individuelles et groupées)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send-device', [NotificationController::class, 'sendToDevice'])->name('notifications.send-device');
    Route::post('/notifications/send-multiple', [NotificationController::class, 'sendToMultipleDevices'])->name('notifications.send-multiple');
    Route::post('/notifications/send-all', [NotificationController::class, 'sendToAllDevices'])->name('notifications.send-all');
    Route::post('/notifications/send-topic', [NotificationController::class, 'sendToTopic'])->name('notifications.send-topic');
    Route::post('/notifications/send-topics', [NotificationController::class, 'sendToMultipleTopics'])->name('notifications.send-topics');

    // Routes pour les notifications par type d'alerte
    Route::get('/notifications-by-alert', [NotificationController::class, 'indexByAlert'])->name('notifications.by-alert.index');
    Route::post('/notifications-by-alert/send', [NotificationController::class, 'sendByAlertExpiration'])->name('notifications.by-alert.send');
});

Route::prefix('call-center')->group(function () {
    Route::middleware(['guest:call_center'])->group(function () {
        Route::get('/login', [CallCenterAuthController::class, 'showLogin'])->name('call-center.login');
        Route::post('/login', [CallCenterAuthController::class, 'login'])->name('call-center.authenticate');
    });

    Route::middleware(['auth:call_center'])->group(function () {
        Route::get('/dashboard', [CallCenterSpaceController::class, 'dashboard'])->name('call-center.dashboard');
        Route::get('/users', [CallCenterSpaceController::class, 'users'])->name('call-center.users');
        Route::get('/users/{user}/alertes', [CallCenterSpaceController::class, 'userAlerts'])->name('call-center.users.alerts');
        Route::get('/users/{user}/vehicules', [CallCenterSpaceController::class, 'userVehicules'])->name('call-center.users.vehicules');
        Route::get('/users/{user}/annonces', [CallCenterSpaceController::class, 'userAnnonces'])->name('call-center.users.annonces');
        Route::get('/users/{user}/autodocs', [CallCenterSpaceController::class, 'userAutodocs'])->name('call-center.users.autodocs');
        Route::get('/users/{user}/autodocs/{autodoc}/details', [CallCenterSpaceController::class, 'autodocDetails'])->name('call-center.users.autodocs.details');
        Route::get('/professionnels', [CallCenterSpaceController::class, 'professionnels'])->name('call-center.professionnels');
        Route::get('/professionnels/{professionnel}/details', [CallCenterSpaceController::class, 'professionnelDetails'])->name('call-center.professionnels.details');
        Route::get('/vehicules', [CallCenterSpaceController::class, 'vehicules'])->name('call-center.vehicules');
        Route::get('/vehicules/{vehicule}/details', [CallCenterSpaceController::class, 'vehiculeDetails'])->name('call-center.vehicules.details');
        Route::get('/station-services', [CallCenterSpaceController::class, 'stationServices'])->name('call-center.station-services');
        Route::get('/station-de-lavages', [CallCenterSpaceController::class, 'stationDeLavages'])->name('call-center.station-de-lavages');
        Route::get('/annonces', [CallCenterSpaceController::class, 'annonces'])->name('call-center.annonces');
        Route::get('/annonce-concessionnaires', [CallCenterSpaceController::class, 'annonceConcessionnaires'])->name('call-center.annonce-concessionnaires');
        Route::get('/annonce-etablissements', [CallCenterSpaceController::class, 'annonceEtablissements'])->name('call-center.annonce-etablissements');
        Route::get('/concessionnaires', [CallCenterSpaceController::class, 'concessionnaires'])->name('call-center.concessionnaires');
        Route::get('/etablissements', [CallCenterSpaceController::class, 'etablissements'])->name('call-center.etablissements');
        Route::post('/etablissements/{etablissement}/suivi-appel', [CallCenterSpaceController::class, 'updateEtablissementCallFollowUp'])->name('call-center.etablissements.suivi-appel');
        Route::get('/etablissements/{etablissement}/articles', [CallCenterSpaceController::class, 'etablissementArticles'])->name('call-center.etablissements.articles');
        Route::get('/etablissements/{etablissement}/promotions', [CallCenterSpaceController::class, 'etablissementPromotions'])->name('call-center.etablissements.promotions');
        Route::get('/etablissements/{etablissement}/abonnements', [CallCenterSpaceController::class, 'etablissementAbonnements'])->name('call-center.etablissements.abonnements');
        Route::get('/autodocs', [CallCenterSpaceController::class, 'autodocs'])->name('call-center.autodocs');
        Route::post('/logout', [CallCenterAuthController::class, 'logout'])->name('call-center.logout');
    });
});

Route::get('/', [AuthController::class, 'showlogin'])->name('login');

Route::get('/login', [AuthController::class, 'showlogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('logins');

Route::get('/register', [AuthController::class, 'showregister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('registers');


// Mot de passe oublié - Affichage du formulaire
Route::get('/password/forget', [AuthController::class, 'showpasswordforget'])->name('password.forget');

// Mot de passe oublié - Envoi OTP
Route::post('/password/forget', [AuthController::class, 'postPasswordForget'])->name('post-password.forget');

// Saisie OTP
Route::get('/otp', function() { return view('auth.otp'); })->name('auth.otp');
Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');

// Réinitialisation du mot de passe
Route::get('/password/reset', function() { return view('auth.password_reset'); })->name('password.reset.form');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset.submit');
