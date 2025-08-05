<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Anfrage;
use App\Models\Markt;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Märkte mit aktiven Terminen laden und nach Terminen gruppieren
        $aktiveMaerkte = Markt::whereHas('termine', function ($query) {
                $query->where('start', '>', now());
            })
            ->with(['termine' => function ($query) {
                $query->where('start', '>', now())->orderBy('start');
            }])
            ->get()
            ->sortBy(function ($markt) {
                return $markt->termine->first()?->start;
            });

        $stats = [
            Stat::make('Neue Anfragen', Anfrage::where('created_at', '>=', now()->subDays(7))->count())
                ->description('In den letzten 7 Tagen')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->url(route('filament.admin.resources.anfrage.index')),
        ];

        // Aktive Märkte anzeigen (maximal 3)
        foreach ($aktiveMaerkte->take(3) as $markt) {
            $termine = $markt->termine;
            $termineText = $termine->map(function ($termin) {
                return Carbon::parse($termin->start)->format('d.m.Y');
            })->join(', ');

            $stats[] = Stat::make('Markt', $markt->name)
                ->description($termineText)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->url(route('filament.admin.resources.markt.edit', ['record' => $markt->slug]));
        }

        // Falls mehr als 3 aktive Märkte vorhanden sind, Übersicht anzeigen
        if ($aktiveMaerkte->count() > 3) {
            $stats[] = Stat::make('Weitere Märkte', ($aktiveMaerkte->count() - 3) . ' weitere')
                ->description('Märkte mit Terminen')
                ->descriptionIcon('heroicon-m-ellipsis-horizontal')
                ->color('gray')
                ->url(route('filament.admin.resources.markt.index'));
        }

        return $stats;
    }
}
