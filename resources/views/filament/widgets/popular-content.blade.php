<x-filament-widgets::widget>
    <x-filament::section
        heading="Meest bekeken"
        description="Unieke bezoekers per dag. Ingelogde gebruikers en bots tellen niet mee."
    >
        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($this->getGroups() as $group)
                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $group['title'] }}</h3>

                    @foreach (['month' => $this->getMonthLabel(), 'allTime' => 'Altijd'] as $period => $label)
                        <div class="mt-4">
                            <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                Top 5 · {{ $label }}
                            </p>

                            @if (count($group[$period]))
                                <ol class="mt-2 divide-y divide-gray-100 dark:divide-white/5">
                                    @foreach ($group[$period] as $index => $row)
                                        <li class="flex items-center gap-3 py-2 text-sm">
                                            <span class="w-4 shrink-0 text-gray-400">{{ $index + 1 }}</span>
                                            <a href="{{ $row['url'] }}" class="min-w-0 flex-1 truncate text-gray-950 hover:text-primary-600 hover:underline dark:text-white dark:hover:text-primary-400">
                                                {{ $row['title'] }}
                                            </a>
                                            <x-filament::badge color="gray">
                                                {{ number_format($row['views'], 0, ',', '.') }}
                                            </x-filament::badge>
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nog geen weergaven.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
