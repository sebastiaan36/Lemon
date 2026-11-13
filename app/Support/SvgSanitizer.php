<?php

namespace App\Support;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Strips scripts, event handlers, foreignObject, external references and
 * other active content from uploaded SVG files, so an SVG opened directly
 * from our domain cannot run JavaScript.
 */
class SvgSanitizer
{
    public static function isSvg(?string $extension, ?string $mimeType = null): bool
    {
        return in_array(strtolower((string) $extension), ['svg', 'svgz'], true)
            || str_contains(strtolower((string) $mimeType), 'svg');
    }

    public static function sanitize(string $svg): ?string
    {
        $sanitizer = new Sanitizer;
        $sanitizer->removeRemoteReferences(true);

        $clean = $sanitizer->sanitize($svg);

        return is_string($clean) && trim($clean) !== '' ? $clean : null;
    }

    /**
     * Sanitize an SVG stored on a filesystem disk in place. Compressed SVGZ
     * files and files that cannot be parsed are deleted and rejected.
     *
     * @throws ValidationException
     */
    public static function sanitizeStoredFile(string $disk, string $path): void
    {
        $storage = Storage::disk($disk);
        $clean = str_ends_with(strtolower($path), '.svgz') ? null : self::sanitize((string) $storage->get($path));

        if ($clean === null) {
            $storage->delete($path);

            throw ValidationException::withMessages([
                'file' => 'Dit SVG-bestand kon niet veilig worden verwerkt. Exporteer het opnieuw als standaard (niet-gecomprimeerde) SVG.',
            ]);
        }

        $storage->put($path, $clean);
    }
}
