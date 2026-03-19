<x-filament-widgets::widget class="h-full">
    <x-filament::section class="h-full flex flex-col">
        <x-slot name="heading">
            <div class="flex items-center gap-2.5">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-500 text-white shadow-sm">
                    <x-heroicon-s-megaphone class="w-4 h-4"/>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-semibold text-gray-950 dark:text-white leading-tight">Announcements</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 leading-tight">
                        {{ $announcements->count() }} recent
                        @if($announcements->where('is_pinned', true)->count())
                            &bull; {{ $announcements->where('is_pinned', true)->count() }} pinned
                        @endif
                    </span>
                </div>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <div class="flex items-center gap-2">
                <a href="{{ $listUrl }}" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 font-medium transition-colors">
                    View all
                    <x-heroicon-m-arrow-right class="w-3 h-3"/>
                </a>
                <a href="{{ $createUrl }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-xs font-semibold shadow-sm transition-colors">
                    <x-heroicon-m-plus class="w-3.5 h-3.5"/>
                    Post
                </a>
            </div>
        </x-slot>

        <div class="flex-1 flex flex-col pt-1">
            @if($announcements->isEmpty())
                <div class="flex-1 flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-2">
                        <x-heroicon-o-megaphone class="w-5 h-5 text-gray-400"/>
                    </div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No announcements yet</p>
                    <p class="text-xs text-gray-400 mt-1">Be the first to post something to the team.</p>
                    <a href="{{ $createUrl }}" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-500 hover:bg-primary-600 text-white text-xs font-semibold transition-colors">
                        <x-heroicon-m-plus class="w-3.5 h-3.5"/>
                        Post Announcement
                    </a>
                </div>
            @else
                <div class="flex-1 flex flex-col divide-y divide-gray-100 dark:divide-white/5 -mt-2 -mx-6">
                    @foreach($announcements as $announcement)
                        <a href="{{ $getViewUrl($announcement) }}" class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            {{-- Pin / avatar indicator --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @if($announcement->is_pinned)
                                    <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                        <x-heroicon-s-bookmark class="w-3.5 h-3.5 text-amber-500"/>
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                        <x-heroicon-m-chat-bubble-oval-left class="w-3.5 h-3.5 text-primary-500"/>
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                        {{ $announcement->title }}
                                    </p>
                                    @if($announcement->is_pinned)
                                        <span class="flex-shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                            Pinned
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                    {{ $announcement->excerpt }}
                                </p>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <span class="flex items-center gap-1 text-xs text-gray-400">
                                        <x-heroicon-m-user-circle class="w-3 h-3"/>
                                        {{ $announcement->author?->name ?? 'Unknown' }}
                                    </span>
                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                    <span class="text-xs text-gray-400">
                                        {{ $announcement->created_at->diffForHumans() }}
                                    </span>
                                    @if($announcement->comments_count > 0)
                                        <span class="text-gray-300 dark:text-gray-600">·</span>
                                        <span class="flex items-center gap-1 text-xs text-gray-400">
                                            <x-heroicon-m-chat-bubble-left class="w-3 h-3"/>
                                            {{ $announcement->comments_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <x-heroicon-m-chevron-right class="flex-shrink-0 w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-primary-400 transition-colors mt-1"/>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
