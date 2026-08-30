<?php

namespace App\Services;

use App\Models\NotificationCampaign;
use App\Models\NotificationCampaignLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationCampaignService
{
    public function __construct(private FirebaseNotificationService $firebaseService)
    {
    }

    public function audienceQuery(string $audienceType, array $filters = []): Builder
    {
        $query = User::query()
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        if ($audienceType === NotificationCampaign::AUDIENCE_SELECTED_USERS) {
            $userIds = collect($filters['user_ids'] ?? [])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (!empty($userIds)) {
                $query->whereIn('users.id', $userIds);
            }
        }

        if ($audienceType === NotificationCampaign::AUDIENCE_ALERT_EXPIRATION) {
            $query->join('alerts', 'alerts.user_id', '=', 'users.id');

            if (!empty($filters['type_alert_id'])) {
                $query->where('alerts.type_alert_id', $filters['type_alert_id']);
            }

            $mode = $filters['expires_mode'] ?? 'in_days';
            if ($mode === 'today') {
                $query->whereDate('alerts.date_fin', now()->toDateString());
            } elseif ($mode === 'between') {
                if (!empty($filters['date_from'])) {
                    $query->whereDate('alerts.date_fin', '>=', $filters['date_from']);
                }
                if (!empty($filters['date_to'])) {
                    $query->whereDate('alerts.date_fin', '<=', $filters['date_to']);
                }
            } else {
                $days = max(0, (int) ($filters['days'] ?? 0));
                $query->whereDate('alerts.date_fin', now()->addDays($days)->toDateString());
            }

            $query->select('users.*')
                ->addSelect('alerts.id as alert_id', 'alerts.type_alert_id as alert_type_alert_id')
                ->orderBy('alerts.date_fin');
        }

        $this->whereIfUserColumn($query, 'statut', $filters['statut'] ?? null);
        $this->whereIfUserColumn($query, 'ville_id', $filters['ville_id'] ?? null);
        $this->whereIfUserColumn($query, 'commune_id', $filters['commune_id'] ?? null);
        if (!empty($filters['user_id']) && is_numeric($filters['user_id'])) {
            $query->where('users.id', (int) $filters['user_id']);
        }

        if (!empty($filters['fcm_token'])) {
            $query->where('users.fcm_token', 'like', '%' . trim($filters['fcm_token']) . '%');
        }
        if (!empty($filters['keyword'])) {
            $keyword = '%' . trim($filters['keyword']) . '%';
            $query->where(function ($q) use ($keyword) {
                foreach (['nom', 'prenoms', 'name', 'email', 'telephone', 'mobile'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $q->orWhere('users.' . $column, 'like', $keyword);
                    }
                }
            });
        }

        return $query;
    }

    public function preview(string $audienceType, array $filters = [], int $limit = 50): Collection
    {
        return $this->audienceQuery($audienceType, $filters)
            ->limit($limit)
            ->get();
    }

    public function countAudience(string $audienceType, array $filters = []): int
    {
        if ($audienceType === NotificationCampaign::AUDIENCE_ALERT_EXPIRATION) {
            return (clone $this->audienceQuery($audienceType, $filters))->distinct('users.id')->count('users.id');
        }

        return $this->audienceQuery($audienceType, $filters)->count();
    }

    public function send(NotificationCampaign $campaign): array
    {
        $campaign->update([
            'status' => NotificationCampaign::STATUS_SENDING,
            'last_error' => null,
        ]);

        $totalTargets = 0;
        $successCount = 0;
        $failureCount = 0;

        try {
            $this->audienceQuery($campaign->audience_type, $campaign->audience_filters ?? [])
                ->orderBy('users.id')
                ->chunk(400, function ($users) use ($campaign, &$totalTargets, &$successCount, &$failureCount) {
                    $tokens = $users->pluck('fcm_token')->filter()->unique()->values()->all();
                    $totalTargets += count($tokens);

                    if (empty($tokens)) {
                        return;
                    }

                    $result = $this->firebaseService->sendToMultipleDevices(
                        $tokens,
                        $campaign->title,
                        $campaign->body,
                        $this->buildPushData($campaign)
                    );

                    $batchSuccess = (int) ($result['success_count'] ?? 0);
                    $batchFailure = (int) ($result['failure_count'] ?? max(0, count($tokens) - $batchSuccess));
                    $successCount += $batchSuccess;
                    $failureCount += $batchFailure;

                    foreach ($users as $user) {
                        NotificationCampaignLog::create([
                            'notification_campaign_id' => $campaign->id,
                            'user_id' => $user->id,
                            'alert_id' => $user->alert_id ?? null,
                            'type_alert_id' => $user->alert_type_alert_id ?? null,
                            'fcm_token' => $user->fcm_token,
                            'status' => ($result['success'] ?? false) ? 'sent' : 'failed',
                            'error_message' => ($result['success'] ?? false) ? null : ($result['message'] ?? 'Erreur inconnue'),
                            'sent_at' => now(),
                        ]);
                    }
                });

            if ($totalTargets === 0) {
                $message = 'Aucun usager avec un token FCM valide trouve pour cette campagne.';

                $campaign->update([
                    'status' => NotificationCampaign::STATUS_FAILED,
                    'sent_at' => now(),
                    'total_targets' => 0,
                    'success_count' => 0,
                    'failure_count' => 0,
                    'last_error' => $message,
                ]);

                return [
                    'success' => false,
                    'message' => $message,
                    'total_targets' => 0,
                    'success_count' => 0,
                    'failure_count' => 0,
                ];
            }

            if ($successCount === 0) {
                $message = "Notification non envoyee: {$failureCount} echec(s) sur {$totalTargets} cible(s).";

                $campaign->update([
                    'status' => NotificationCampaign::STATUS_FAILED,
                    'sent_at' => now(),
                    'total_targets' => $totalTargets,
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'last_error' => $message,
                ]);

                return [
                    'success' => false,
                    'message' => $message,
                    'total_targets' => $totalTargets,
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                ];
            }

            $campaign->update([
                'status' => NotificationCampaign::STATUS_SENT,
                'sent_at' => now(),
                'total_targets' => $totalTargets,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);

            return [
                'success' => true,
                'message' => "Notification envoyee a {$successCount} usager(s).",
                'total_targets' => $totalTargets,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ];
        } catch (\Throwable $e) {
            $campaign->update([
                'status' => NotificationCampaign::STATUS_FAILED,
                'total_targets' => $totalTargets,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'last_error' => $e->getMessage(),
            ]);

            report($e);

            return [
                'success' => false,
                'message' => "Erreur lors de l'envoi: " . $e->getMessage(),
                'total_targets' => $totalTargets,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ];
        }
    }

    private function whereIfUserColumn(Builder $query, string $column, $value): void
    {
        if ($value !== null && $value !== '' && Schema::hasColumn('users', $column)) {
            $query->where('users.' . $column, $value);
        }
    }
    private function buildPushData(NotificationCampaign $campaign): array
    {
        return array_filter([
            'type' => 'notification_campaign',
            'notification_campaign_id' => (string) $campaign->id,
            'audience_type' => $campaign->audience_type,
            'image_url' => $campaign->image_url,
            'action_url' => $campaign->action_url,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
