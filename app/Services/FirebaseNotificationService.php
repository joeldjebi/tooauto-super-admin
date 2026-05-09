<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\Message;
use Exception;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $firebaseCredentialsPath = storage_path('app/firebase/touauto-4f8df-firebase-adminsdk-fbsvc-da305bbcbd.json');
            
            if (!file_exists($firebaseCredentialsPath)) {
                throw new Exception('Fichier de credentials Firebase introuvable');
            }

            $factory = (new Factory)->withServiceAccount($firebaseCredentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'initialisation de Firebase: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer une notification à un seul appareil
     *
     * @param string $deviceToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = []): array
    {
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData($data);

            $result = $this->messaging->send($message);

            return [
                'success' => true,
                'message' => 'Notification envoyée avec succès',
                'message_id' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification à plusieurs appareils
     *
     * @param array $deviceTokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToMultipleDevices(array $deviceTokens, string $title, string $body, array $data = []): array
    {
        try {
            if (empty($deviceTokens)) {
                return [
                    'success' => false,
                    'message' => 'Aucun token d\'appareil fourni'
                ];
            }

            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            // Utiliser sendMulticast pour envoyer à plusieurs appareils
            $report = $this->messaging->sendMulticast($message, $deviceTokens);

            $successCount = $report->successes()->count();
            $failureCount = $report->failures()->count();

            return [
                'success' => true,
                'message' => "Notifications envoyées: {$successCount} réussies, {$failureCount} échouées",
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'total' => count($deviceTokens)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification à un topic (groupe)
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        try {
            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $result = $this->messaging->send($message);

            return [
                'success' => true,
                'message' => 'Notification envoyée au topic avec succès',
                'message_id' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification à plusieurs topics
     *
     * @param array $topics
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToMultipleTopics(array $topics, string $title, string $body, array $data = []): array
    {
        try {
            if (empty($topics)) {
                return [
                    'success' => false,
                    'message' => 'Aucun topic fourni'
                ];
            }

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($topics as $topic) {
                $result = $this->sendToTopic($topic, $title, $body, $data);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
                $results[] = $result;
            }

            return [
                'success' => $failureCount === 0,
                'message' => "Notifications envoyées: {$successCount} réussies, {$failureCount} échouées",
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'total' => count($topics),
                'results' => $results
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ];
        }
    }
}

