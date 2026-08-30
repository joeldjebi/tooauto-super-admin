<?php

namespace App\Console\Commands;

use App\Models\NotificationCampaign;
use App\Services\NotificationCampaignService;
use Illuminate\Console\Command;

class SendDueNotificationCampaigns extends Command
{
    protected $signature = 'notification-campaigns:send-due {--limit=50 : Nombre maximum de campagnes a traiter}';

    protected $description = 'Envoie les campagnes de notification programmées dont la date est arrivée';

    public function handle(NotificationCampaignService $service): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $campaigns = NotificationCampaign::due()->orderBy('scheduled_at')->limit($limit)->get();

        if ($campaigns->isEmpty()) {
            $this->info('Aucune campagne de notification à envoyer.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            try {
                $result = $service->send($campaign);
                $line = "#{$campaign->id} {$campaign->title}: {$result['message']}";
                $result['success'] ? $this->info($line) : $this->error($line);
            } catch (\Throwable $exception) {
                $campaign->update([
                    'status' => NotificationCampaign::STATUS_FAILED,
                    'last_error' => $exception->getMessage(),
                ]);

                report($exception);
                $this->error("#{$campaign->id} {$campaign->title}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
