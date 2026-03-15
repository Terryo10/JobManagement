<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Invoice Report Configuration</x-slot>
            <x-slot name="description">Select a date range and filters. The AI will generate a Markdown invoice & revenue report.</x-slot>

            <form wire:submit.prevent="generate">
                {{ $this->form }}

                <div class="mt-4 flex flex-wrap gap-3">
                    <x-filament::button type="submit" icon="heroicon-o-sparkles" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="generate">Generate Report</span>
                        <span wire:loading wire:target="generate">Generating...</span>
                    </x-filament::button>

                    @if($reportMarkdown)
                        <x-filament::button
                            type="button"
                            wire:click="revise"
                            color="warning"
                            icon="heroicon-o-arrow-path"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="revise">Revise with AI</span>
                            <span wire:loading wire:target="revise">Revising...</span>
                        </x-filament::button>
                    @endif
                </div>
            </form>
        </x-filament::section>

        @if($reportMarkdown)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>Generated Invoice Report</span>
                        <x-filament::button
                            size="sm"
                            color="{{ $isEditing ? 'success' : 'gray' }}"
                            icon="{{ $isEditing ? 'heroicon-o-check' : 'heroicon-o-pencil' }}"
                            wire:click="toggleEdit"
                        >
                            {{ $isEditing ? 'Done Editing' : 'Edit Markdown' }}
                        </x-filament::button>
                    </div>
                </x-slot>

                @if($isEditing)
                    <textarea
                        wire:model.live="reportMarkdown"
                        class="w-full h-96 font-mono text-sm p-4 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
                    ></textarea>
                @else
                    <div class="prose prose-sm dark:prose-invert max-w-none p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-auto max-h-[70vh]">
                        {!! \Illuminate\Support\Str::markdown($reportMarkdown) !!}
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
