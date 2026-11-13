<?php

namespace App\Providers;

use App\Models\User;
use App\Services\BunnyStream;
use App\Support\MediaResolver;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Facades\Curator;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Maximum upload size for media, in kilobytes (50 MB).
     */
    private const int MEDIA_MAX_UPLOAD_SIZE = 50 * 1024;

    public function register(): void
    {
        $this->app->scoped(MediaResolver::class);
        $this->app->bind(BunnyStream::class, fn (): BunnyStream => BunnyStream::fromConfig());
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMediaUploads();
        $this->configureAuthorization();
    }

    /**
     * Only users who may enter the admin panel can preview concept (unpublished) content.
     */
    protected function configureAuthorization(): void
    {
        Gate::define('view-concepts', fn (User $user): bool => $user->canAccessPanel(Filament::getPanel('admin')));
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureMediaUploads(): void
    {
        Curator::maxSize(self::MEDIA_MAX_UPLOAD_SIZE);

        CuratorPicker::configureUsing(
            fn (CuratorPicker $picker): CuratorPicker => $picker->maxSize(self::MEDIA_MAX_UPLOAD_SIZE),
        );
    }
}
