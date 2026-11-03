<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanNotificationImports extends Command
{
    protected $signature = 'notification-imports:clean {--hours=24 : Age minimum (en heures) des aperçus à supprimer}';

    protected $description = "Supprime les aperçus d'import de notifications jamais validés ni annulés";

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $limit = now()->subHours(max(1, (int) $this->option('hours')))->getTimestamp();
        $deleted = 0;

        foreach ($disk->files('notification-imports') as $file) {
            if ($disk->lastModified($file) < $limit) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("{$deleted} aperçu(s) d'import supprimé(s).");

        return self::SUCCESS;
    }
}
