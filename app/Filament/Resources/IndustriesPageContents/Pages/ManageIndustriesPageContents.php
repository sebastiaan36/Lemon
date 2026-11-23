<?php

namespace App\Filament\Resources\IndustriesPageContents\Pages;

use App\Filament\Resources\IndustriesPageContents\IndustriesPageContentResource;
use App\Models\IndustriesPageContent;
use App\Models\Industry;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManageIndustriesPageContents extends EditRecord
{
    protected static string $resource = IndustriesPageContentResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = IndustriesPageContent::getSingleton();

        parent::mount($singleton->id);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * De branches staan in hun eigen tabel; laad ze in de repeater.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['industries'] = Industry::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Industry $industry): array => [
                'id' => $industry->getKey(),
                ...collect($industry->getFillable())
                    ->mapWithKeys(fn (string $field): array => [$field => $industry->getAttribute($field)])
                    ->except('sort_order')
                    ->all(),
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * Sla de pagina-teksten op en synchroniseer de branches: volgorde volgt de
     * repeater, weggehaalde branches worden verwijderd.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $industries = array_values($data['industries'] ?? []);
        unset($data['industries']);

        return DB::transaction(function () use ($record, $data, $industries): Model {
            $record->update($data);

            $keptIds = [];

            foreach ($industries as $index => $item) {
                $id = $item['id'] ?? null;
                unset($item['id']);

                $industry = filled($id) ? Industry::query()->find($id) : null;
                $industry ??= new Industry;

                $industry->fill([...$item, 'sort_order' => $index + 1])->save();

                $keptIds[] = $industry->getKey();
            }

            Industry::query()->whereKeyNot($keptIds)->get()->each->delete();

            return $record;
        });
    }
}
