<div
    x-data="{
        selected: $wire.entangle('selected'),
        multiple: {{ $isMultiple ? 'true' : 'false' }},
        handleItemClick: function (media = null) {
            if (! media) return;

            if (this.isSelected(media.id)) {
                this.removeFromSelection(media.id);
                return;
            }

            if (! this.multiple) {
                this.selected = [];
            }

            this.addToSelection(media);
        },
        hasSelection: function () {
            return this.selected.length > 0;
        },
        isSelected: function (id = null) {
            if (! this.hasSelection()) return false;

            return this.selected.find(function (item) {
                return item.id == id;
            }) !== undefined;
        },
        addToSelection: function (media = null) {
            if (! media) return;

            this.selected.push(media);
        },
        removeFromSelection: function (id = null) {
            if (! id) return;

            this.selected = this.selected.filter(function (item) {
                return item.id != id;
            });
        },
    }"
    class="curator-panel"
    style="display: flex; flex-direction: column; overflow: hidden; height: 82vh;"
>
    <!-- Insert selected files bar (floating) -->
    <div
        x-show="hasSelection()"
        class="curator-panel-controls"
        style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 20; display: flex; justify-content: center; pointer-events: none;"
        x-transition:enter="ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
    >
        <div style="background: #1f2937; border-radius: 0.75rem; padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem; pointer-events: auto;">
            {{ $this->insertMedia }}
            <x-filament::button color="gray" size="sm" x-on:click="selected = []">
                {{ trans('curator::views.panel.deselect_all') }}
            </x-filament::button>
        </div>
    </div>

    <!-- 1. Upload area -->
    <div
        class="curator-panel-upload"
        style="flex-shrink: 0; border-bottom: 1px solid #e5e7eb; padding: 0.75rem 1rem;"
    >
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            @if ($this->addFilesAction->isVisible())
                {{ $this->addFilesAction }}
                {{ $this->addInsertFilesAction }}
            @endif
        </div>
        {{ $this->form }}
    </div>

    <!-- 2. Search bar -->
    <div
        style="flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 1rem; border-bottom: 1px solid #e5e7eb; background: rgba(0,0,0,0.03);"
    >
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            @if($currentPage < $lastPage)
                <x-filament::button size="xs" color="gray" wire:click="loadMoreFiles()">
                    {{ trans('curator::views.panel.load_more') }}
                </x-filament::button>
            @endif
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <label style="position: relative; display: flex; align-items: center;">
                <span style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0);">{{ trans('curator::views.panel.search_label') }}</span>
                <x-filament::input.wrapper
                    prefix-icon="heroicon-s-magnifying-glass"
                    prefix-icon-alias="curator::icons.search"
                >
                    <x-filament::input
                        type="search"
                        placeholder="{{ trans('curator::views.panel.search_placeholder') }}"
                        wire:model.live.debounce.500ms="search"
                    />
                </x-filament::input.wrapper>
            </label>
            <x-filament::icon-button
                x-on:click="close()"
                icon="heroicon-o-x-mark"
                color="gray"
            />
        </div>
    </div>

    <!-- 3. Gallery (scrollable) -->
    <div style="flex: 1; min-height: 0; overflow-y: auto; padding: 1rem;">
        <ul @class(['text-sm flex items-center', 'mb-4' => filled($breadcrumbs)])>
            @if ($breadcrumbs)
                @foreach($breadcrumbs as $breadcrumb)
                    <li wire:key="breadcrumb-{{ $breadcrumb['path'] }}">
                        @if ($loop->last)
                            <span style="opacity: 0.5;">{{ $breadcrumb['label'] }}</span>
                        @else
                            <button type="button" wire:click.prevent="handleDirectoryChange('{{ $breadcrumb['path'] }}')">{{ $breadcrumb['label'] }}</button>
                            <span>/&nbsp;</span>
                        @endif
                    </li>
                @endforeach
            @endif
        </ul>

        <ul class="curator-picker-grid" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem;">
            @if ($subDirectories)
                @foreach($subDirectories as $dir)
                    <li wire:key="dir-{{ $dir['name'] }}" style="position: relative; aspect-ratio: 1 / 1; overflow: hidden;">
                        <button
                            type="button"
                            wire:click="handleDirectoryChange('{{ $dir['path'] }}')"
                            class="block w-full h-full overflow-hidden bg-gray-200 rounded-md dark:bg-gray-900"
                        >
                            <div class="grid place-content-center place-items-center w-full h-full text-xs">
                                <x-filament::icon alias="curator::icons.folder" icon="heroicon-o-folder" class="w-12 h-12 opacity-20" />
                                <span>{{ $dir['label'] }}</span>
                            </div>
                        </button>
                    </li>
                @endforeach
            @endif

            @forelse ($files as $file)
                <li wire:key="media-{{ $file['id'] }}" style="position: relative; aspect-ratio: 1 / 1; overflow: hidden;" class="group">
                    <button
                        type="button"
                        x-on:click="handleItemClick(@js($file))"
                        @class([
                            'block w-full h-full overflow-hidden bg-gray-700 rounded-md checkered',
                            'p-2' => curator()->isSvg($file['ext']),
                        ])
                    >
                        @if (curator()->isVideo($file['ext']))
                            <video
                                src="{{ $file['url'] }}"
                                preload="metadata"
                                muted
                                playsinline
                                onloadedmetadata="this.currentTime=0.001"
                                style="width: 100%; height: 100%; object-fit: cover; display: block;"
                            ></video>
                        @else
                            <x-curator::display
                                :item="$file"
                                :src="$file['thumbnail_url']"
                                :alt="$file['alt'] ?? ''"
                                icon-classes="size-12"
                                width="200"
                                height="200"
                            />
                        @endif
                    </button>

                    <button
                        type="button"
                        x-on:click="removeFromSelection('{{ $file['id'] }}')"
                        x-show="isSelected('{{ $file['id'] }}')"
                        x-cloak
                        style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; border-radius: 0.375rem;"
                        class="text-primary-600 bg-primary-500/20 ring-2 ring-primary-500"
                    >
                        <span class="flex items-center justify-center w-8 h-8 text-white rounded-full bg-primary-500 drop-shadow">
                            <x-filament::icon alias="curator::icons.check" icon="heroicon-s-check" class="w-5 h-5" />
                        </span>
                        <span style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0);">{{ trans('curator::views.panel.deselect') }}</span>
                    </button>

                    <div style="position: absolute; top: 4px; right: 4px;" class="opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto group-focus-within:opacity-100 group-focus-within:pointer-events-auto transition">
                        <div style="background: #1f2937; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem;">
                            <x-filament-actions::group
                                :actions="[
                                    ($this->viewItemAction)(['item' => $file]),
                                    ($this->downloadItemAction)(['item' => $file]),
                                    ($this->editItemAction)(['item' => $file]),
                                    ($this->destroyItemAction)(['item' => $file]),
                                ]"
                                color="primary"
                                icon-size="sm"
                                dropdown-placement="bottom-start"
                                dropdown-width="max-w-48"
                            />
                        </div>
                    </div>

                    <p style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem 0.25rem 0.25rem; border-radius: 0 0 0.375rem 0.375rem; color: white; font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); pointer-events: none; opacity: 0; transition: opacity 0.15s;"
                        class="group-hover:opacity-100 group-focus-within:opacity-100">
                        {{ $file['pretty_name'] }}
                    </p>
                </li>
            @empty
                @empty($subDirectories)
                    <li style="grid-column: span 3;">
                        {{ trans('curator::views.panel.empty') }}
                    </li>
                @endempty
            @endforelse
        </ul>
    </div>

    <x-filament-actions::modals />
</div>
