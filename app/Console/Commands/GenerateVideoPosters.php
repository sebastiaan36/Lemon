<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Support\VideoPoster;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:video-posters {--force : Ook video\'s die al een poster hebben opnieuw verwerken}')]
#[Description('Maakt lichte WebP-posters voor video\'s, zichtbaar zolang de video nog laadt')]
class GenerateVideoPosters extends Command
{
    public function handle(): int
    {
        $videos = Media::query()->where('type', 'like', 'video/%')->get()
            ->filter(fn (Media $media): bool => $this->option('force') || ! VideoPoster::exists($media));

        $created = 0;
        $skipped = 0;

        $this->withProgressBar($videos, function (Media $media) use (&$created, &$skipped): void {
            VideoPoster::generate($media) === [] ? $skipped++ : $created++;
        });

        $this->newLine(2);
        $this->info("Klaar: {$created} posters gemaakt, {$skipped} zonder bronbeeld (Bunny nog niet klaar en geen ffmpeg).");

        return self::SUCCESS;
    }
}
