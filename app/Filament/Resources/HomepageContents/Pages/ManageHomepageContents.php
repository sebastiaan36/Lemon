<?php

namespace App\Filament\Resources\HomepageContents\Pages;

use App\Filament\Resources\HomepageContents\HomepageContentResource;
use App\Models\HomepageContent;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ManageHomepageContents extends EditRecord
{
    protected static string $resource = HomepageContentResource::class;

    /**
     * Laad altijd de enkele homepage-rij (singleton-patroon).
     */
    public function mount(int|string $record = null): void
    {
        $singleton = HomepageContent::getSingleton();

        parent::mount($singleton->id);
    }

    protected function getHeaderActions(): array
    {
        return []; // Geen delete-knop voor de singleton
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        Log::info('HomepageContent save() called');

        try {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
            Log::info('HomepageContent save() completed successfully');
        } catch (Halt $e) {
            Log::warning('HomepageContent save() halted (Halt exception)');
            throw $e;
        } catch (ValidationException $e) {
            Log::warning('HomepageContent save() validation failed: ' . json_encode($e->errors()));
            throw $e;
        } catch (Throwable $e) {
            Log::error('HomepageContent save() threw: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info('HomepageContent mutateFormDataBeforeSave called, keys: ' . implode(', ', array_keys($data)));

        return $data;
    }
}
