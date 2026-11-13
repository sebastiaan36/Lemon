<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\BunnyStream;
use App\Support\VideoStream;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:bunny-stream {--force : Ook video\'s die al op Bunny staan opnieuw uploaden}')]
#[Description('Zet video\'s uit de media-bibliotheek over naar Bunny Stream en werkt de encoding-status bij')]
class SyncBunnyStream extends Command
{
    public function handle(BunnyStream $stream): int
    {
        if (! $stream->isConfigured()) {
            $this->error('Bunny Stream is niet geconfigureerd: vul BUNNY_STREAM_API_KEY, BUNNY_STREAM_LIBRARY_ID en BUNNY_STREAM_CDN_HOSTNAME in .env.');

            return self::FAILURE;
        }

        $videos = Media::query()->where('type', 'like', 'video/%')->get();
        $failed = 0;

        $toUpload = $videos->filter(fn (Media $media): bool => $this->option('force') || VideoStream::needsUpload($media));

        if ($toUpload->isNotEmpty()) {
            $this->info("Uploaden: {$toUpload->count()} video('s)");

            $this->withProgressBar($toUpload, function (Media $media) use (&$failed): void {
                try {
                    VideoStream::upload($media);
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->warn("Upload mislukt: {$media->path} ({$e->getMessage()})");
                }
            });
            $this->newLine(2);
        }

        foreach ($videos->filter(fn (Media $media): bool => VideoStream::isPending($media)) as $media) {
            try {
                VideoStream::refresh($media);
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Status ophalen mislukt: {$media->path} ({$e->getMessage()})");
            }
        }

        $ready = $videos->filter(fn (Media $media): bool => VideoStream::isReady($media))->count();
        $pending = $videos->filter(fn (Media $media): bool => VideoStream::isPending($media))->count();

        $this->info("Klaar: {$ready} beschikbaar via Bunny Stream, {$pending} nog aan het encoderen, {$failed} mislukt.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
