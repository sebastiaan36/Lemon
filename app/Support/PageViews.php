<?php

namespace App\Support;

use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\Job;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Telt hoe vaak cases, blogs en jobs bekeken worden.
 *
 * Eén bezoeker telt maximaal één keer per item per dag (via de sessie).
 * Ingelogde gebruikers (het team) en bots tellen niet mee.
 */
class PageViews
{
    /** @var array<class-string<Model>, string> */
    private const TYPES = [
        CaseStudy::class => 'case',
        Blog::class => 'blog',
        Job::class => 'job',
    ];

    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|whatsapp|headless|lighthouse|pingdom|uptime|monitor|curl|wget|python|java\/|go-http|okhttp|axios|node-fetch/i';

    public static function record(Request $request, Model $model): void
    {
        if (! self::shouldCount($request)) {
            return;
        }

        $type = self::type($model::class);
        $today = now()->toDateString();

        if ($request->hasSession()) {
            $key = "page_views.{$type}.{$model->getKey()}";

            if ($request->session()->get($key) === $today) {
                return;
            }

            $request->session()->put($key, $today);
        }

        DB::table('page_views')->upsert(
            [['viewable_type' => $type, 'viewable_id' => $model->getKey(), 'viewed_on' => $today, 'views' => 1]],
            ['viewable_type', 'viewable_id', 'viewed_on'],
            ['views' => DB::raw('page_views.views + 1')],
        );
    }

    /**
     * De meest bekeken items, met het aantal weergaven in `views_count`.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return Collection<int, TModel>
     */
    public static function top(string $modelClass, ?CarbonInterface $since = null, int $limit = 5): Collection
    {
        $model = new $modelClass;

        $totals = DB::table('page_views')
            ->select('viewable_id')
            ->selectRaw('SUM(views) as views_count')
            ->where('viewable_type', self::type($modelClass))
            ->when($since, fn ($query) => $query->where('viewed_on', '>=', $since->toDateString()))
            ->groupBy('viewable_id');

        return $modelClass::query()
            ->joinSub($totals, 'totals', 'totals.viewable_id', '=', $model->getQualifiedKeyName())
            ->select($model->qualifyColumn('*'), 'totals.views_count')
            ->orderByDesc('totals.views_count')
            ->limit($limit)
            ->get();
    }

    private static function shouldCount(Request $request): bool
    {
        if ($request->user() !== null) {
            return false;
        }

        // Inertia/browsers die een link alvast ophalen zijn nog geen echte weergave.
        if (str_contains((string) ($request->header('Purpose').$request->header('Sec-Purpose')), 'prefetch')) {
            return false;
        }

        $agent = (string) $request->userAgent();

        return $agent !== '' && ! preg_match(self::BOT_PATTERN, $agent);
    }

    private static function type(string $modelClass): string
    {
        return self::TYPES[$modelClass] ?? throw new InvalidArgumentException("{$modelClass} wordt niet geteld.");
    }
}
