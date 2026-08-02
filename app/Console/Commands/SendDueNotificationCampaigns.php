<?php

namespace App\Console\Commands;

use App\Models\NotificationCampaign;
use App\Services\NotificationCampaignService;
use Illuminate\Console\Command;

class SendDueNotificationCampaigns extends Command
{
    protected $signature = 'notification-campaigns:send-due';

    protected $description = 'Envoie les campagnes de notification programmées dont la date est arrivée';

    public function handle(NotificationCampaignService $service): int
    {
        $campaigns = NotificationCampaign::due()->orderBy('scheduled_at')->get();

        if ($campaigns->isEmpty()) {
            $this->info('Aucune campagne de notification à envoyer.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $result = $service->send($campaign);
            $line = "#{$campaign->id} {$campaign->title}: {$result['message']}";
            $result['success'] ? $this->info($line) : $this->error($line);
        }

        return self::SUCCESS;
    }
}