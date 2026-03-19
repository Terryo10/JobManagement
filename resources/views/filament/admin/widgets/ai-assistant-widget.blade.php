<x-filament-widgets::widget>

    {{-- ── Widget Card ─────────────────────────────────────────────────────────── --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-1.5">
                <x-heroicon-o-sparkles class="w-4 h-4 text-primary-500" />
                <span class="text-sm font-semibold text-gray-950 dark:text-white">AI Assistant</span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="text-xs text-gray-400 dark:text-gray-500">Powered by Gemini</span>
        </x-slot>

        <div class="space-y-3 pt-1">

            {{-- Topic Pills --}}
            <div class="flex flex-wrap gap-1.5">
                @foreach(\App\Filament\Admin\Widgets\AiAssistantWidget::getTopics() as $key => $topic)
                    <button
                        wire:click="selectTopic('{{ $key }}')"
                        type="button"
                        @class([
                            'px-3 py-1 text-xs font-medium rounded-full border transition-all duration-150 cursor-pointer',
                            'bg-primary-500 border-primary-500 text-white shadow-sm' => $selectedTopic === $key,
                            'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-primary-400 hover:text-primary-600 dark:hover:border-primary-400 dark:hover:text-primary-400' => $selectedTopic !== $key,
                        ])
                    >
                        {{ $topic['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Action row --}}
            <div class="flex items-center gap-3">
                <button
                    wire:click="getSummary"
                    wire:loading.attr="disabled"
                    wire:target="getSummary"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg
                           bg-primary-600 hover:bg-primary-700 active:bg-primary-800
                           text-white disabled:opacity-50 disabled:cursor-not-allowed
                           transition-colors shadow-sm cursor-pointer"
                >
                    <x-heroicon-o-sparkles class="w-3.5 h-3.5" wire:loading.remove wire:target="getSummary" />
                    <svg wire:loading wire:target="getSummary" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span wire:loading.remove wire:target="getSummary">Get Summary</span>
                    <span wire:loading wire:target="getSummary">Generating…</span>
                </button>

                @if(!$isModalOpen && $summary)
                    <button
                        wire:click="$set('isModalOpen', true)"
                        type="button"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline cursor-pointer"
                    >
                        Reopen last chat
                    </button>
                @endif
            </div>

        </div>
    </x-filament::section>

    {{-- ── Modal Overlay ───────────────────────────────────────────────────────── --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">

            {{-- Backdrop --}}
            <div
                wire:click="closeModal"
                class="absolute inset-0 bg-gray-950/75"
            ></div>

            {{-- Panel --}}
            <div
                class="relative z-10 flex flex-col w-full max-w-2xl
                       rounded-xl shadow-xl overflow-hidden
                       ring-1 ring-gray-950/10 dark:ring-white/10
                       bg-white dark:bg-gray-900"
                style="max-height: 88vh;"
                @click.stop
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-5 py-3.5 shrink-0
                            border-b border-gray-200 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-800">
                    <div class="flex items-center gap-2.5">
                        <x-heroicon-o-sparkles class="w-4 h-4 text-primary-500" />
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ \App\Filament\Admin\Widgets\AiAssistantWidget::getTopics()[$selectedTopic]['label'] ?? '' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                     bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300
                                     ring-1 ring-primary-200 dark:ring-primary-800">
                            Live Data
                        </span>
                    </div>
                    <button
                        wire:click="closeModal"
                        type="button"
                        class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500
                               hover:text-gray-700 dark:hover:text-gray-200
                               hover:bg-gray-100 dark:hover:bg-gray-700
                               transition-colors cursor-pointer"
                        aria-label="Close"
                    >
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>

                {{-- Scrollable Body --}}
                <div class="flex-1 overflow-y-auto bg-white dark:bg-gray-900" id="ai-chat-scroll-area">

                    {{-- Summary Block --}}
                    @if($summary)
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-1.5 mb-3">
                                <x-heroicon-m-chart-bar class="w-3.5 h-3.5 text-primary-500" />
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Overview
                                </span>
                            </div>
                            <div class="prose prose-sm dark:prose-invert max-w-none
                                        prose-p:text-gray-700 dark:prose-p:text-gray-300
                                        prose-li:text-gray-700 dark:prose-li:text-gray-300
                                        prose-strong:text-gray-900 dark:prose-strong:text-white
                                        prose-headings:text-gray-900 dark:prose-headings:text-white">
                                {!! \Illuminate\Support\Str::markdown($summary) !!}
                            </div>
                        </div>
                    @endif

                    {{-- Chat Messages --}}
                    @if(count($messages) > 0)
                        <div class="px-5 py-4 space-y-4">
                            @foreach($messages as $message)
                                @if($message['role'] === 'user')
                                    <div class="flex justify-end">
                                        <div class="max-w-[80%] px-3.5 py-2.5 rounded-2xl rounded-tr-sm shadow-sm
                                                    bg-primary-600 text-white text-sm leading-relaxed">
                                            {{ $message['content'] }}
                                        </div>
                                    </div>
                                @else
                                    <div class="flex justify-start">
                                        <div class="max-w-[85%] px-3.5 py-2.5 rounded-2xl rounded-tl-sm shadow-sm
                                                    bg-gray-100 dark:bg-gray-800
                                                    ring-1 ring-gray-200 dark:ring-gray-700">
                                            <div class="prose prose-sm dark:prose-invert max-w-none
                                                        prose-p:text-gray-800 dark:prose-p:text-gray-200
                                                        prose-li:text-gray-800 dark:prose-li:text-gray-200
                                                        prose-strong:text-gray-900 dark:prose-strong:text-white
                                                        [&>p]:mb-1.5 [&>ul]:mt-1 [&>ul]:space-y-0.5">
                                                {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Typing indicator --}}
                            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                                <div class="px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm
                                            bg-gray-100 dark:bg-gray-800
                                            ring-1 ring-gray-200 dark:ring-gray-700">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-bounce [animation-delay:0ms]"></span>
                                        <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-bounce [animation-delay:150ms]"></span>
                                        <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500 animate-bounce [animation-delay:300ms]"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($summary)
                        <div class="px-5 pt-3 pb-2">
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic">
                                Ask a follow-up question below…
                            </p>
                        </div>
                    @endif

                </div>

                {{-- Input Area --}}
                <div class="shrink-0 px-4 py-3
                            border-t border-gray-200 dark:border-gray-700
                            bg-gray-50 dark:bg-gray-800"
                    x-data
                    x-init="
                        $wire.on('sendMessage', () => {
                            $nextTick(() => {
                                const el = document.getElementById('ai-chat-scroll-area');
                                if (el) el.scrollTop = el.scrollHeight;
                            });
                        });
                    "
                >
                    <div class="flex items-center gap-2">
                        <input
                            wire:model="messageInput"
                            wire:keydown.enter.prevent="sendMessage"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                            type="text"
                            placeholder="Ask a follow-up question…"
                            autocomplete="off"
                            class="flex-1 text-sm px-3.5 py-2 rounded-lg
                                   bg-white dark:bg-gray-900
                                   border border-gray-300 dark:border-gray-600
                                   text-gray-900 dark:text-white
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                   disabled:opacity-50 transition-all"
                        />
                        <button
                            wire:click="sendMessage"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                            type="button"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg shrink-0
                                   bg-primary-600 hover:bg-primary-700
                                   text-white disabled:opacity-50 disabled:cursor-not-allowed
                                   transition-colors shadow-sm cursor-pointer"
                            aria-label="Send"
                        >
                            <svg wire:loading.remove wire:target="sendMessage" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                            <svg wire:loading wire:target="sendMessage" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-center text-gray-400 dark:text-gray-500">
                        Gemini has access to live system data for this topic
                    </p>
                </div>

            </div>
        </div>
    @endif

</x-filament-widgets::widget>
