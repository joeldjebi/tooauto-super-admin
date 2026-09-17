<?php

namespace App\Console\Commands;

use App\Services\NotificationCampaignService;
use Illuminate\Console\Command;

class SendDueNotificationCampaigns extends Command
{
    protected $signature = 'notification-campaigns:send-due {--limit=50 : Nombre maximum de campagnes a traiter}';

    protected $description = 'Envoie les campagnes de notification programmées dont la date est arrivée';

    public function handle(NotificationCampaignService $service): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $campaigns = $service->sendDueCampaigns($limit);

        if ($campaigns->isEmpty()) {
            $this->info('Aucune campagne de notification à envoyer.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $item) {
            $campaign = $item['campaign'];
            $result = $item['result'];
            $line = "#{$campaign->id} {$campaign->title}: {$result['message']}";
            $result['success'] ? $this->info($line) : $this->error($line);
        }

        return self::SUCCESS;
    }
}
