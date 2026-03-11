<?php

namespace App\Filament\Marketing\Widgets;

use App\Models\Lead;
use App\Models\NetworkingEvent;
use App\Models\Proposal;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class MarketingCalendarWidget extends FullCalendarWidget
{
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = 'full';

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,listWeek',
            ],
            'height'   => 680,
            'navLinks' => true,
        ];
    }

    public function fetchEvents(array $info): array
    {
        $events = [];

        // --- Networking Events ---
        NetworkingEvent::where(function ($query) use ($info) {
            $query->whereBetween('start_date', [$info['start'], $info['end']])
                  ->orWhereBetween('end_date', [$info['start'], $info['end']]);
        })->get()->each(function (NetworkingEvent $event) use (&$events) {
            $events[] = [
                'id'              => 'event-' . $event->id,
                'title'           => 'Event: ' . $event->name,
                'start'           => $event->start_date->toDateString(),
                'end'             => $event->end_date ? $event->end_date->addDay()->toDateString() : $event->start_date->addDay()->toDateString(),
                'allDay'          => true,
                'backgroundColor' => '#8b5cf6', // Violet
                'borderColor'     => '#8b5cf6',
                'textColor'       => '#ffffff',
                'url'             => route('filament.marketing.resources.networking-events.edit', $event->id),
            ];
        });

        // --- Lead Follow-ups ---
        Lead::whereNotNull('follow_up_date')
            ->whereBetween('follow_up_date', [$info['start'], $info['end']])
            ->get()->each(function (Lead $lead) use (&$events) {
                $events[] = [
                    'id'              => 'lead-' . $lead->id,
                    'title'           => 'Follow-up: ' . $lead->contact_name . ($lead->company_name ? ' (' . $lead->company_name . ')' : ''),
                    'start'           => $lead->follow_up_date->toDateString(),
                    'end'             => $lead->follow_up_date->addDay()->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => '#3b82f6', // Blue
                    'borderColor'     => '#3b82f6',
                    'textColor'       => '#ffffff',
                    'url'             => route('filament.marketing.resources.leads.edit', $lead->id),
                ];
            });

        // --- Proposals (Valid Until) ---
        Proposal::whereNotNull('valid_until')
            ->whereBetween('valid_until', [$info['start'], $info['end']])
            ->get()->each(function (Proposal $proposal) use (&$events) {
                $events[] = [
                    'id'              => 'proposal-' . $proposal->id,
                    'title'           => 'Expiration: ' . $proposal->title,
                    'start'           => $proposal->valid_until->toDateString(),
                    'end'             => $proposal->valid_until->addDay()->toDateString(),
                    'allDay'          => true,
                    'backgroundColor' => '#f59e0b', // Amber
                    'borderColor'     => '#f59e0b',
                    'textColor'       => '#ffffff',
                    'url'             => route('filament.marketing.resources.proposals.edit', $proposal->id),
                ];
            });

        return $events;
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }
}
