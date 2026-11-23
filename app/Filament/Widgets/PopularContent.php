<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Blogs\BlogResource;
use App\Filament\Resources\Cases\CaseStudyResource;
use App\Filament\Resources\Jobs\JobResource;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\Job;
use App\Support\PageViews;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

/**
 * Top 5 meest bekeken cases, jobs en blogs: deze maand en sinds de start van het tellen.
 */
class PopularContent extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.popular-content';

    public function getMonthLabel(): string
    {
        return ucfirst(now()->locale('nl')->translatedFormat('F Y'));
    }

    /**
     * @return list<array{title: string, month: list<array{title: string, url: string, views: int}>, allTime: list<array{title: string, url: string, views: int}>}>
     */
    public function getGroups(): array
    {
        $groups = [
            ['Cases', CaseStudy::class, fn (CaseStudy $case): string => $case->name, CaseStudyResource::class],
            ['Jobs', Job::class, fn (Job $job): string => $job->title, JobResource::class],
            ['Blogs', Blog::class, fn (Blog $blog): string => $blog->title, BlogResource::class],
        ];

        return array_map(fn (array $group): array => [
            'title' => $group[0],
            'month' => $this->rows($group[1], $group[2], $group[3], now()->startOfMonth()),
            'allTime' => $this->rows($group[1], $group[2], $group[3]),
        ], $groups);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  class-string  $resource
     * @return list<array{title: string, url: string, views: int}>
     */
    private function rows(string $modelClass, callable $title, string $resource, mixed $since = null): array
    {
        return PageViews::top($modelClass, $since)
            ->map(fn (Model $model): array => [
                'title' => $title($model),
                'url' => $resource::getUrl('edit', ['record' => $model]),
                'views' => (int) $model->getAttribute('views_count'),
            ])
            ->values()
            ->all();
    }
}
