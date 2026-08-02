<?php

namespace App\Services;

use App\Models\MessageConseil;
use App\Models\MessageConseilLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class MessageConseilService
{
    public function __construct(private FirebaseNotificationService $firebaseService)
    {
    }

    public function audienceQuery(array $filters = []): Builder
    {
        $query = User::query()
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '');

        $this->whereIfColumn($query, 'statut', $filters['statut'] ?? null);
        $this->whereIfColumn($query, 'ville_id', $filters['ville_id'] ?? null);
        $this->whereIfColumn($query, 'commune_id', $filters['commune_id'] ?? null);
        $this->whereIfColumn($query, 'commercial_id', $filters['commercial_id'] ?? null);
        $this->whereIfColumn($query, 'indicatif', $filters['indicatif'] ?? null);

        if (!empty($filters['quartier']) && Schema::hasColumn('users', 'quartier')) {
            $query->where('quartier', 'like', '%' . trim($filters['quartier']) . '%');
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . trim($filters['keyword']) . '%';
            $query->where(function ($q) use ($keyword) {
                foreach (['nom', 'prenoms', 'name', 'email', 'mobile', 'telephone'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $q->orWhere($column, 'like', $keyword);
                    }
                }
            });
        }

        if (!empty($filters['created_from']) && Schema::hasColumn('users', 'created_at')) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to']) && Schema::hasColumn('users', 'created_at')) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query;
    }

    public function countAudience(array $filters = []): int
    {
        return $this->audienceQuery($filters)->count();
    }

    public function send(MessageConseil $messageConseil): array
    {
        $messageConseil->update([
            'status' => MessageConseil::STATUS_SENDING,
            'last_error' => null,
        ]);

        $totalTargets = 0;
        $successCount = 0;
        $failureCount = 0;

        try {
            $this->audienceQuery($messageConseil->filters ?? [])
                ->select('id', 'fcm_token')
                ->orderBy('id')
                ->chunk(400, function ($users) use ($messageConseil, &$totalTargets, &$successCount, &$failureCount) {
                    $tokens = $users->pluck('fcm_token')->filter()->unique()->values()->all();
                    $totalTargets += count($tokens);

                    if (empty($tokens)) {
                        return;
                    }

                    $result = $this->firebaseService->sendToMultipleDevices(
                        $tokens,
                        $messageConseil->title,
                        $messageConseil->body,
                        $this->buildPushData($messageConseil)
                    );

                    $batchSuccess = (int) ($result['success_count'] ?? 0);
                    $batchFailure = (int) ($result['failure_count'] ?? max(0, count($tokens) - $batchSuccess));
                    $successCount += $batchSuccess;
                    $failureCount += $batchFailure;

                    foreach ($users as $user) {
                        MessageConseilLog::create([
                            'message_conseil_id' => $messageConseil->id,
                            'user_id' => $user->id,
                            'fcm_token' => $user->fcm_token,
                            'status' => ($result['success'] ?? false) ? 'sent' : 'failed',
                            'error_message' => ($result['success'] ?? false) ? null : ($result['message'] ?? 'Erreur inconnue'),
                            'sent_at' => now(),
                        ]);
                    }
                });

            $messageConseil->update([
                'status' => MessageConseil::STATUS_SENT,
                'sent_at' => now(),
                'total_targets' => $totalTargets,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);

            return [
                'success' => true,
                'message' => "Message conseil envoyé à {$successCount} usager(s).",
                'total_targets' => $totalTargets,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ];
        } catch (\Throwable $e) {
            $messageConseil->update([
                'status' => MessageConseil::STATUS_FAILED,
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

    private function whereIfColumn(Builder $query, string $column, $value): void
    {
        if ($value !== null && $value !== '' && Schema::hasColumn('users', $column)) {
            $query->where($column, $value);
        }
    }

    private function buildPushData(MessageConseil $messageConseil): array
    {
        return array_filter([
            'type' => 'message_conseil',
            'message_conseil_id' => (string) $messageConseil->id,
            'image_url' => $messageConseil->image_url,
            'action_url' => $messageConseil->action_url,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
