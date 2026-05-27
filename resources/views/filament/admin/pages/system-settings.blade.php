<x-filament-panels::page>

    <x-filament::section>
        <x-slot name="heading">Communication Channels</x-slot>
        <x-slot name="description">
            Control platform-wide communication channel settings. Changes take effect immediately across all dashboards.
        </x-slot>

        {{-- SMS Toggle --}}
        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-6 py-5 dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                {{-- Icon --}}
                @if ($smsEnabled)
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <x-heroicon-o-chat-bubble-bottom-center-text class="h-6 w-6" />
                    </div>
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <x-heroicon-o-no-symbol class="h-6 w-6" />
                    </div>
                @endif

                {{-- Text --}}
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                        SMS Notifications
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        @if ($smsEnabled)
                            SMS is <span class="font-medium text-emerald-600 dark:text-emerald-400">active</span> — messages will be delivered via Infobip.
                        @else
                            SMS is <span class="font-medium text-red-600 dark:text-red-400">disabled</span> — no SMS will be sent from any dashboard.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Toggle Button --}}
            @if ($smsEnabled)
                <button
                    type="button"
                    wire:click="toggleSms"
                    wire:loading.attr="disabled"
                    class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-emerald-500 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    role="switch"
                    aria-checked="true"
                    aria-label="Toggle SMS notifications"
                >
                    <span class="pointer-events-none inline-block h-6 w-6 translate-x-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            @else
                <button
                    type="button"
                    wire:click="toggleSms"
                    wire:loading.attr="disabled"
                    class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-300 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-600 dark:focus:ring-offset-gray-900"
                    role="switch"
                    aria-checked="false"
                    aria-label="Toggle SMS notifications"
                >
                    <span class="pointer-events-none inline-block h-6 w-6 translate-x-0 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            @endif
        </div>

        {{-- Warning banner when disabled --}}
        @if (! $smsEnabled)
            <div class="mt-4 flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                <x-heroicon-s-exclamation-triangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" />
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        SMS is currently disabled platform-wide
                    </p>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300/80">
                        All SMS sending from every dashboard (Admin, Marketing, Staff, Accountant) is blocked.
                        This includes automated notifications, field worker alerts, and manually composed messages.
                        Toggle the switch above to re-enable.
                    </p>
                </div>
            </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
