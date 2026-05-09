<?php

namespace App\Http\Controllers;

use App\Services\FirebaseNotificationService;
use App\Models\User;
use App\Models\Alert;
use App\Models\Type_alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Afficher la page d'envoi de notifications (individuelles et groupées)
     */
    public function index()
    {
        $data['title'] = 'Notifications Push Firebase';
        $data['menu'] = 'notifications';

        // Récupérer la liste des utilisateurs avec leurs tokens FCM
        $data['users'] = User::select('id', 'nom', 'prenoms', 'email', 'mobile', 'fcm_token')
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->orderBy('nom')
            ->orderBy('prenoms')
            ->get();

        return view('notifications.index', $data);
    }

    /**
     * Afficher la page d'envoi de notifications par type d'alerte
     */
    public function indexByAlert()
    {
        $data['title'] = 'Notifications par Type d\'Alerte';
        $data['menu'] = 'notifications-type-alerte';

        // Récupérer les types d'alertes
        $data['type_alerts'] = Type_alert::select('id', 'libelle')
            ->orderBy('libelle')
            ->get();

        return view('notifications.by_alert', $data);
    }

    /**
     * Envoyer une notification à un appareil unique
     */
    public function sendToDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Récupérer le token FCM de l'utilisateur sélectionné
            $user = User::find($request->user_id);

            if (!$user || empty($user->fcm_token)) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'L\'utilisateur sélectionné n\'a pas de token FCM valide');
                return back();
            }

            $data = [];
            if ($request->has('data') && is_string($request->data)) {
                $data = json_decode($request->data, true) ?? [];
            } elseif ($request->has('data') && is_array($request->data)) {
                $data = $request->data;
            }

            $result = $this->firebaseService->sendToDevice(
                $user->fcm_token,
                $request->title,
                $request->body,
                $data
            );

            if ($result['success']) {
                session()->flash('type', 'alert-success');
                session()->flash('message', $result['message']);
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Envoyer une notification à plusieurs appareils
     */
    public function sendToMultipleDevices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Récupérer les tokens FCM des utilisateurs sélectionnés
            $users = User::whereIn('id', $request->user_ids)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->pluck('fcm_token')
                ->toArray();

            if (empty($users)) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Aucun token FCM valide trouvé pour les utilisateurs sélectionnés');
                return back();
            }

            $tokens = $users;

            $data = [];
            if ($request->has('data') && is_string($request->data)) {
                $data = json_decode($request->data, true) ?? [];
            } elseif ($request->has('data') && is_array($request->data)) {
                $data = $request->data;
            }

            $result = $this->firebaseService->sendToMultipleDevices(
                $tokens,
                $request->title,
                $request->body,
                $data
            );

            if ($result['success']) {
                session()->flash('type', 'alert-success');
                session()->flash('message', $result['message']);
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Envoyer une notification à un topic
     */
    public function sendToTopic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = [];
            if ($request->has('data') && is_string($request->data)) {
                $data = json_decode($request->data, true) ?? [];
            } elseif ($request->has('data') && is_array($request->data)) {
                $data = $request->data;
            }

            $result = $this->firebaseService->sendToTopic(
                $request->topic,
                $request->title,
                $request->body,
                $data
            );

            if ($result['success']) {
                session()->flash('type', 'alert-success');
                session()->flash('message', $result['message']);
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Envoyer une notification à plusieurs topics
     */
    public function sendToMultipleTopics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topics' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Convertir la chaîne de topics séparés par des virgules ou des retours à la ligne en tableau
            $topics = preg_split('/[,\n\r]+/', $request->topics);
            $topics = array_map('trim', $topics);
            $topics = array_filter($topics); // Supprimer les valeurs vides

            if (empty($topics)) {
                session()->flash('type', 'alert-danger');
                session()->flash('message', 'Aucun topic valide fourni');
                return back();
            }

            $data = [];
            if ($request->has('data') && is_string($request->data)) {
                $data = json_decode($request->data, true) ?? [];
            } elseif ($request->has('data') && is_array($request->data)) {
                $data = $request->data;
            }

            $result = $this->firebaseService->sendToMultipleTopics(
                $topics,
                $request->title,
                $request->body,
                $data
            );

            if ($result['success']) {
                session()->flash('type', 'alert-success');
                session()->flash('message', $result['message']);
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Envoyer des notifications aux utilisateurs dont les alertes expirent dans X jours
     */
    public function sendByAlertExpiration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_alert_id' => 'required|exists:type_alerts,id',
            'days_before_expiration' => 'required|integer|min:0|max:365',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Calculer la date d'expiration cible
            $targetDate = Carbon::now()->addDays($request->days_before_expiration)->format('Y-m-d');

            // Récupérer les alertes qui expirent dans X jours pour le type d'alerte sélectionné
            $alerts = Alert::where('type_alert_id', $request->type_alert_id)
                ->whereDate('date_fin', $targetDate)
                ->whereNotNull('user_id')
                ->with('user')
                ->get();

            if ($alerts->isEmpty()) {
                session()->flash('type', 'alert-warning');
                session()->flash('message', 'Aucune alerte trouvée qui expire dans ' . $request->days_before_expiration . ' jour(s) pour ce type d\'alerte.');
                return back();
            }

            // Récupérer les tokens FCM uniques des utilisateurs concernés
            $userIds = $alerts->pluck('user_id')->unique()->filter();
            $users = User::whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->pluck('fcm_token')
                ->toArray();

            if (empty($users)) {
                session()->flash('type', 'alert-warning');
                session()->flash('message', 'Aucun utilisateur avec un token FCM valide trouvé pour ces alertes.');
                return back();
            }

            $data = [];
            if ($request->has('data') && is_string($request->data)) {
                $data = json_decode($request->data, true) ?? [];
            } elseif ($request->has('data') && is_array($request->data)) {
                $data = $request->data;
            }

            // Envoyer les notifications
            $result = $this->firebaseService->sendToMultipleDevices(
                $users,
                $request->title,
                $request->body,
                $data
            );

            if ($result['success']) {
                session()->flash('type', 'alert-success');
                session()->flash('message', $result['message'] . ' (' . count($users) . ' utilisateur(s) notifié(s) sur ' . $alerts->count() . ' alerte(s) trouvée(s))');
            } else {
                session()->flash('type', 'alert-danger');
                session()->flash('message', $result['message']);
            }
        } catch (\Exception $e) {
            session()->flash('type', 'alert-danger');
            session()->flash('message', 'Erreur: ' . $e->getMessage());
        }

        return back();
    }
}