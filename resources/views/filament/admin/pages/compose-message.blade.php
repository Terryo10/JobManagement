<x-filament-panels::page>

    <x-filament::section>
        <x-slot name="heading">Compose Message</x-slot>
        <x-slot name="description">
            Send a custom email, WhatsApp, or SMS to one or more staff members directly from the system.
        </x-slot>

        <form wire:submit="send">
            {{ $this->form }}

            <div class="mt-6 flex items-center justify-end gap-3">
                {{-- Sending indicator --}}
                <span wire:loading wire:target="send" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="h-4 w-4 animate-spin text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Sending…
                </span>

                <x-filament::button
                    type="submit"
                    icon="heroicon-o-paper-airplane"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="send"
                >
                    Send Message
                </x-filament::button>
            </div>
        </form>

    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-panels::page>
