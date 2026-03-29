<x-filament-panels::page>

    <x-filament::section>
        <x-slot name="heading">Compose Message</x-slot>
        <x-slot name="description">
            Send a custom email, WhatsApp, or SMS to one or more staff members directly from the system.
        </x-slot>

        <form wire:submit="send">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                    Send Message
                </x-filament::button>
            </div>
        </form>

    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-panels::page>
