<?php

namespace App\Providers;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Facades\Curator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Maximum upload size for media, in kilobytes (50 MB).
     */
    private const int MEDIA_MAX_UPLOAD_SIZE = 50 * 1024;

    public function register(): void {}

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMediaUploads();
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
