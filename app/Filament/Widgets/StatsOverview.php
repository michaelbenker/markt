<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Anfrage;
use App\Models\Termin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Alle Märkte mit aktiven Terminen
        $aktiveMaerkte = DB::table('termin')
            ->join('markt', 'termin.markt_id', '=', 'markt.id')
            ->where('termin.start', '>', now())
            ->select('markt.name', 'termin.start', 'markt.slug')
            ->orderBy('termin.start')
            ->get();

        $stats = [
            Stat::make('Neue Anfragen', Anfrage::where('created_at', '>=', now()->subDays(7))->count())
                ->description('In den letzten 7 Tagen')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->url(route('filament.admin.resources.anfrage.index')),
        ];

        // Weitere aktive Märkte (maximal 3 zusätzliche)
        foreach ($aktiveMaerkte->skip(1)->take(3) as $markt) {
            $startDate = Carbon::parse($markt->start);

            $stats[] = Stat::make('Markt', $markt->name)
                ->description($startDate->format('d.m.Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->url(route('filament.admin.resources.markt.edit', ['record' => $markt->slug]));
        }

        // Falls mehr als 4 aktive Märkte vorhanden sind, Übersicht anzeigen
        if ($aktiveMaerkte->count() > 4) {
            $stats[] = Stat::make('Weitere Märkte', $aktiveMaerkte->count() - 4 . ' weitere')
                ->description('Märkte mit Terminen')
                ->descriptionIcon('heroicon-m-ellipsis-horizontal')
                ->color('gray')
                ->url(route('filament.admin.resources.markt.index'));
        }

        return $stats;
    }
}
