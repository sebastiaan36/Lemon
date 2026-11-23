<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Blogs\BlogResource;
use App\Filament\Resources\Cases\CaseStudyResource;
use App\Filament\Resources\Jobs\JobResource;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-actions';

    /**
     * @return list<array{label: string, url: string, icon: Heroicon}>
     */
    public function getActions(): array
    {
        return [
            ['label' => 'Nieuwe case', 'url' => CaseStudyResource::getUrl('create'), 'icon' => Heroicon::OutlinedBriefcase],
            ['label' => 'Nieuwe blog', 'url' => BlogResource::getUrl('create'), 'icon' => Heroicon::OutlinedNewspaper],
            ['label' => 'Nieuwe job', 'url' => JobResource::getUrl('create'), 'icon' => Heroicon::OutlinedUserPlus],
        ];
    }
}
