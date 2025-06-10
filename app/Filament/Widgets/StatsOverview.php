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
        return [
            Stat::make('Neue Anfragen', Anfrage::where('created_at', '>=', now()->subDays(7))->count())
                ->description('In den letzten 7 Tagen')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->url(route('filament.admin.resources.anfrage.index')),
            // Stat::make('Ungelesene Nachrichten', DB::table('notifications')
            //     ->where('notifiable_id', Auth::id())
            //     ->where('notifiable_type', get_class(Auth::user()))
            //     ->whereNull('read_at')
            //     ->count())
            //     ->description('Benachrichtigungen')
            //     ->descriptionIcon('heroicon-m-bell')
            //     ->color('warning')
            //     ->url(route('filament.admin.pages.notifications')),
            Stat::make('Nächster Markt', function () {
                $nextTermin = Termin::where('start', '>', now())
                    ->orderBy('start')
                    ->first();
                if (!$nextTermin) return 'Keine';

                $startDate = Carbon::parse($nextTermin->start);
                return $nextTermin->markt->name . ' (' . $startDate->format('d.m.Y') . ')';
            })
                ->description('Termin')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url(route('filament.admin.resources.markt.edit', ['record' => Termin::where('start', '>', now())->orderBy('start')->first()?->markt?->slug ?? 0])),
        ];
    }
}
