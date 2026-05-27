<div class="prose dark:prose-invert max-w-none p-4 bg-gray-50/50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-800 leading-relaxed text-gray-800 dark:text-gray-200">
    @if(!empty($content))
        {!! \Illuminate\Support\Str::markdown($content) !!}
    @else
        <p class="text-gray-400 italic">No content available for this report.</p>
    @endif
</div>
