<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;

class AnnouncementsWidget extends Widget
{
    protected static ?int $sort = -1;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.announcements-widget';

    protected function getViewData(): array
    {
        $announcements = Announcement::with('author')
            ->withCount('comments')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->limit(1)
            ->get();

        return [
            'announcements' => $announcements,
            'listUrl'       => route('filament.marketing.resources.announcements.index'),
            'createUrl'     => route('filament.marketing.resources.announcements.create'),
            'getViewUrl'    => fn (Announcement $a) => route('filament.marketing.resources.announcements.view', $a),
        ];
    }
}
