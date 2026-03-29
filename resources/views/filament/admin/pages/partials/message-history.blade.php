<div class="space-y-3">
    @forelse ($logs as $log)
        @php
            $payload    = $log->payload ?? [];
            $subject    = $payload['subject'] ?? '—';
            $body       = $payload['body'] ?? '—';
            $recipient  = $log->notifiable;
            $channelMap = [
                'mail'     => ['label' => 'Email',    'icon' => 'heroicon-m-envelope',   'color' => 'blue'],
                'sms'      => ['label' => 'SMS',      'icon' => 'heroicon-m-device-phone-mobile', 'color' => 'amber'],
                'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'heroicon-m-chat-bubble-left-right', 'color' => 'green'],
            ];
            $ch     = $channelMap[$log->channel] ?? ['label' => ucfirst($log->channel), 'icon' => 'heroicon-m-bell', 'color' => 'gray'];
            $status = match ($log->status) {
                'sent'      => ['label' => 'Sent',    'class' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'],
                'failed'    => ['label' => 'Failed',  'class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'],
                'queued'    => ['label' => 'Queued',  'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                'delivered' => ['label' => 'Delivered','class' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300'],
                default     => ['label' => ucfirst($log->status), 'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'],
            };
        @endphp

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            {{-- Header row --}}
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    {{-- Channel badge --}}
                    @switch($log->channel)
                        @case('mail')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Email
                            </span>
                            @break
                        @case('sms')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                SMS
                            </span>
                            @break
                        @case('whatsapp')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WhatsApp
                            </span>
                            @break
                        @default
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ ucfirst($log->channel) }}
                            </span>
                    @endswitch

                    {{-- Status badge --}}
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </div>

                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                    {{ $log->created_at->diffForHumans() }}
                </span>
            </div>

            {{-- Recipient --}}
            <div class="mt-2 flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-300">
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="font-medium">{{ $recipient?->name ?? 'Unknown' }}</span>
                @if ($recipient?->email)
                    <span class="text-gray-400">&middot; {{ $recipient->email }}</span>
                @endif
            </div>

            {{-- Subject --}}
            @if ($subject !== '—')
                <div class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ $subject }}
                </div>
            @endif

            {{-- Body --}}
            <p class="mt-1 line-clamp-3 text-sm text-gray-500 dark:text-gray-400">
                {{ $body }}
            </p>

            {{-- Error --}}
            @if ($log->error)
                <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">
                    <span class="font-medium">Error:</span> {{ $log->error }}
                </p>
            @endif

            {{-- Action URL --}}
            @if (!empty($payload['action_url']))
                <div class="mt-2">
                    <a href="{{ $payload['action_url'] }}" target="_blank" rel="noopener" class="text-xs text-primary-600 underline dark:text-primary-400">
                        {{ $payload['action_text'] ?? $payload['action_url'] }}
                    </a>
                </div>
            @endif
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-500">
            <svg class="mb-3 h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-sm font-medium">No messages sent yet.</p>
            <p class="mt-1 text-xs">Messages sent via Compose will appear here.</p>
        </div>
    @endforelse
</div>
