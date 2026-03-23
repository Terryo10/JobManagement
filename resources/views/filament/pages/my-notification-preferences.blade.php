<x-filament-panels::page>
    <x-filament::card>
        <p class="mb-6 text-sm text-gray-500 max-w-2xl">
            Choose exactly how you want to be notified for each type of system event. 
            Toggle Email, SMS, WhatsApp, or In-App alerts for your specific workflow.
        </p>

        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" size="lg">
                    Save Preferences
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-panels::page>
