<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left: Configuration Form -->
        <div class="lg:col-span-1 space-y-6">
            <x-filament::card class="relative overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm rounded-xl">
                <!-- Background ambient glow -->
                <div class="absolute -right-16 -top-16 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl pointer-events-none"></div>
                
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-adjustments-horizontal" class="w-5 h-5 text-primary-500" />
                    Report Configuration
                </h3>
                
                <form wire:submit.prevent="generate" class="space-y-6">
                    {{ $this->form }}
                    
                    <x-filament::button type="submit" size="lg" class="w-full shadow-sm hover:translate-y-[-1px] transition-transform duration-200">
                        <span wire:loading.remove wire:target="generate" class="flex items-center gap-1 justify-center">
                            <x-filament::icon icon="heroicon-m-sparkles" class="w-5 h-5" />
                            Generate Daily Report
                        </span>
                        <span wire:loading wire:target="generate" class="flex items-center gap-1 justify-center">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Compiling Data...
                        </span>
                    </x-filament::button>
                </form>
            </x-filament::card>

            <!-- Multi-Turn Refinements and Edits (Only visible if report is generated) -->
            @if(!empty($reportMarkdown))
                <x-filament::card class="border border-gray-100 dark:border-gray-800 shadow-sm rounded-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-m-pencil-square" class="w-5 h-5 text-warning-500" />
                        Refine & Polish
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Toggle Manual Edit -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Edit Markdown Manually</span>
                                <p class="text-xs text-gray-400">Directly modify the report content</p>
                            </div>
                            <input type="checkbox" wire:click="toggleEdit" :checked="$isEditing" class="text-primary-600 focus:ring-primary-500 border-gray-300 rounded cursor-pointer h-5 w-5" />
                        </div>

                        <!-- Chat refinement input -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">AI Refinement Instructions</label>
                            <x-filament::input.wrapper>
                                <x-filament::input 
                                    type="text" 
                                    wire:model="revisionInstructions" 
                                    placeholder="e.g. 'Summarize tasks as a bullet list', 'Make the summary more energetic'" 
                                    class="w-full"
                                />
                            </x-filament::input.wrapper>
                            
                            <x-filament::button 
                                wire:click="revise" 
                                color="warning" 
                                class="w-full mt-2 hover:translate-y-[-1px] transition-transform duration-200"
                            >
                                <span wire:loading.remove wire:target="revise" class="flex items-center gap-1 justify-center">
                                    <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="w-5 h-5" />
                                    Refine with AI
                                </span>
                                <span wire:loading wire:target="revise" class="flex items-center gap-1 justify-center">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Re-processing...
                                </span>
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::card>
            @endif
        </div>

        <!-- Right: Live Markdown Preview / Editor -->
        <div class="lg:col-span-2 space-y-6">
            @if(!empty($reportMarkdown))
                <x-filament::card class="relative overflow-hidden border border-gray-100 dark:border-gray-800 shadow-md rounded-xl p-0">
                    <!-- Glassmorphism header bar for preview -->
                    <div class="flex items-center justify-between px-6 py-4 bg-gray-50/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Report Content Draft
                        </span>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 font-mono">Markdown Format</span>
                        </div>
                    </div>

                    <div class="p-6">
                        @if($isEditing)
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Edit Mode (Manual Markdown override)</label>
                                <x-filament::input.wrapper>
                                    <textarea 
                                        wire:model="reportMarkdown" 
                                        rows="20" 
                                        class="w-full font-mono text-sm p-4 bg-gray-50 dark:bg-gray-900 border-none rounded-lg focus:ring-0 focus:outline-none text-gray-800 dark:text-gray-100 leading-relaxed"
                                        style="resize: vertical;"
                                    ></textarea>
                                </x-filament::input.wrapper>
                            </div>
                        @else
                            <!-- Gorgeous Tailwind Prose Rendered Markdown container -->
                            <div class="prose dark:prose-invert max-w-none prose-headings:font-bold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base prose-a:text-primary-500 hover:prose-a:text-primary-600 prose-blockquote:border-l-primary-500 dark:prose-blockquote:border-l-primary-400 dark:bg-gray-800/40 p-6 rounded-xl border border-gray-50 dark:border-gray-800/80 shadow-inner leading-relaxed text-gray-800 dark:text-gray-200">
                                {!! \Illuminate\Support\Str::markdown($reportMarkdown) !!}
                            </div>
                        @endif
                    </div>
                </x-filament::card>
            @else
                <x-filament::card class="flex flex-col items-center justify-center py-20 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-800/10">
                    <div class="w-16 h-16 rounded-full bg-primary-50 dark:bg-primary-950/30 flex items-center justify-center text-primary-500 mb-4 animate-bounce">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-8 h-8" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No report compiled yet</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
                        Select a date and click "Generate Daily Report" on the left to aggregate system records and draft a professional AI daily work summary.
                    </p>
                </x-filament::card>
            @endif
        </div>
        
    </div>
</x-filament-panels::page>
