<?php

namespace App\Support;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Str;

class RichContent
{
    /**
     * Render RichEditor content to HTML that is safe to inject with v-html:
     * the renderer normalises it through the editor schema and the HTML
     * sanitizer strips scripts, event handlers and unsafe URLs.
     */
    public static function toSafeHtml(mixed $content): string
    {
        if (blank($content)) {
            return '';
        }

        $html = RichContentRenderer::make($content)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public')
            ->toHtml();

        return Str::sanitizeHtml($html);
    }
}
