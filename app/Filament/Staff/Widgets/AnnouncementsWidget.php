<?php

namespace App\Filament\Staff\Widgets;

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
            ->limit(5)
            ->get();

        return [
            'announcements' => $announcements,
            'listUrl'       => route('filament.staff.resources.announcements.index'),
            'createUrl'     => route('filament.staff.resources.announcements.create'),
            'getViewUrl'    => fn (Announcement $a) => route('filament.staff.resources.announcements.view', $a),
        ];
    }
}
