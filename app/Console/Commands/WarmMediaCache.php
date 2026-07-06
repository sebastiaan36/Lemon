<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Support\MediaCacheWarmer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:warm-cache')]
#[Description('Genereert alle thumbnails vooraf zodat de media-bibliotheek in het admin panel direct laadt')]
class WarmMediaCache extends Command
{
    public function handle(): int
    {
        $warmed = 0;
        $skipped = 0;
        $failed = 0;

        $media = Media::query()->get();

        $this->withProgressBar($media, function (Media $item) use (&$warmed, &$skipped, &$failed): void {
            try {
                MediaCacheWarmer::warm($item) ? $warmed++ : $skipped++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("Mislukt: {$item->path} ({$e->getMessage()})");
            }
        });

        $this->newLine(2);
        $this->info("Klaar: {$warmed} bestanden voorbereid, {$skipped} overgeslagen (geen afbeelding), {$failed} mislukt.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
