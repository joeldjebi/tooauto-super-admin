<?php

namespace App\Console\Commands;

use App\Models\MessageConseil;
use App\Services\MessageConseilService;
use Illuminate\Console\Command;

class SendDueMessageConseils extends Command
{
    protected $signature = 'message-conseils:send-due';

    protected $description = 'Envoie les messages conseils programmés dont la date est arrivée';

    public function handle(MessageConseilService $messageConseilService): int
    {
        $messages = MessageConseil::due()->orderBy('scheduled_at')->get();

        if ($messages->isEmpty()) {
            $this->info('Aucun message conseil à envoyer.');
            return self::SUCCESS;
        }

        foreach ($messages as $message) {
            $result = $messageConseilService->send($message);
            $line = "#{$message->id} {$message->title}: {$result['message']}";
            $result['success'] ? $this->info($line) : $this->error($line);
        }

        return self::SUCCESS;
    }
}
