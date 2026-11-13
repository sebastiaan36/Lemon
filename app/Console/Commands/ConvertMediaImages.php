<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Support\ImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:convert-images {--force : Ook afbeeldingen die al varianten hebben opnieuw converteren}')]
#[Description('Maakt WebP- en AVIF-versies van alle bestaande afbeeldingen in de media-bibliotheek')]
class ConvertMediaImages extends Command
{
    public function handle(): int
    {
        $formats = ImageVariants::supportedFormats();

        if ($formats === []) {
            $this->error('De image-driver op deze server ondersteunt geen WebP of AVIF.');

            return self::FAILURE;
        }

        $this->info('Formaten: '.implode(', ', $formats));

        $missing = array_diff(array_keys(ImageVariants::FORMATS), $formats);

        if ($missing !== []) {
            $this->warn('Niet ondersteund op deze server (overgeslagen): '.implode(', ', $missing));
        }

        $converted = 0;
        $skipped = 0;
        $failed = 0;

        $media = Media::query()->get()->filter(
            fn (Media $item): bool => ImageVariants::isConvertible($item)
                && ($this->option('force') || ImageVariants::needsConversion($item)),
        );

        $this->withProgressBar($media, function (Media $item) use (&$converted, &$skipped, &$failed): void {
            try {
                ImageVariants::generate($item) === [] ? $skipped++ : $converted++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("Mislukt: {$item->path} ({$e->getMessage()})");
            }
        });

        $this->newLine(2);
        $this->info("Klaar: {$converted} geconverteerd, {$skipped} zonder kleinere variant, {$failed} mislukt.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
