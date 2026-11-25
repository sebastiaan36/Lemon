<?php

namespace App\Models;

use App\Enums\PillPage;
use App\Models\Concerns\LogsActivity;
use Database\Factories\FloatingPillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'label',
    'href',
    'page',
    'start_section',
    'end_section',
    'avatars',
    'use_team_avatars',
    'desktop_bottom',
    'is_active',
    'sort_order',
])]
class FloatingPill extends Model
{
    /** @use HasFactory<FloatingPillFactory> */
    use HasFactory;

    use LogsActivity;

    public const CACHE_KEY = 'site.floating-pills';

    /** Avatar wanneer er geen eigen foto's zijn gekozen. */
    public const FALLBACK_AVATAR = '/figma-assets/gijs-avatar.png';

    /** Teamfoto's wanneer de homepage er (nog) geen heeft. */
    public const FALLBACK_TEAM_AVATARS = [
        '/figma-assets/1c7ada78-2639-466d-9c86-b655a62ed8d4.png',
        '/figma-assets/f43a4ed5-5188-4fec-83e4-0c7d3aee6388.png',
        '/figma-assets/8b7bd57d-2be8-461e-8982-ebe28d772ec9.png',
        '/figma-assets/00ba73c5-9c31-4545-912f-957df2c1d581.png',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    protected function casts(): array
    {
        return [
            'page' => PillPage::class,
            'avatars' => 'array',
            'use_team_avatars' => 'boolean',
            'desktop_bottom' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Actieve pills voor de site, kort gecachet; opslaan/verwijderen wist de cache.
     *
     * @return list<array{page: string, label: string, href: string, avatars: list<string>, startSection: string, endSection: string, desktopBottom: int}>
     */
    public static function forSite(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            $pills = self::query()->active()->orderBy('sort_order')->orderBy('id')->get();

            $teamAvatars = $pills->contains('use_team_avatars', true)
                ? array_slice(mediaUrls(HomepageContent::query()->first()?->team_avatars), 0, 4) ?: self::FALLBACK_TEAM_AVATARS
                : [];

            primeMedia($pills->pluck('avatars')->all());

            return $pills
                ->map(function (FloatingPill $pill) use ($teamAvatars): array {
                    $avatars = $pill->use_team_avatars ? $teamAvatars : mediaUrls($pill->avatars);

                    return [
                        'page' => $pill->page->value,
                        'label' => $pill->label,
                        'href' => $pill->href,
                        'avatars' => $avatars === [] ? [self::FALLBACK_AVATAR] : $avatars,
                        'startSection' => $pill->start_section,
                        'endSection' => $pill->end_section,
                        'desktopBottom' => $pill->desktop_bottom,
                    ];
                })
                ->values()
                ->all();
        });
    }
}
