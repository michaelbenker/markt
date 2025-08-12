<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\BuchungProtokoll;
use Illuminate\Support\Facades\Auth;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class Buchung extends Model
{
    use HasFilamentComments;
    protected $table = 'buchung';
    protected $fillable = [
        'status',
        'markt_id',
        'termine',
        'standort_id',
        'standplatz',
        'aussteller_id',
        'werbematerial',
        'bemerkung',
    ];

    protected $casts = [
        'termine' => 'array',
        'werbematerial' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });

        static::created(function ($model) {
            BuchungProtokoll::create([
                'buchung_id' => $model->id,
                'user_id' => Auth::id(),
                'aktion' => 'created',
                'from_status' => '',
                'to_status' => $model->status,
                'details' => 'Buchung wurde erstellt.',
            ]);
        });

        static::updating(function ($model) {
            $original = $model->getOriginal();
            // Statuswechsel gesondert loggen
            if (array_key_exists('status', $model->getDirty()) && $original['status'] !== $model->status) {
                BuchungProtokoll::create([
                    'buchung_id' => $model->id,
                    'user_id' => Auth::id(),
                    'aktion' => 'status_changed',
                    'from_status' => $original['status'],
                    'to_status' => $model->status,
                    'details' => 'Status wurde geändert.',
                ]);
            } else {
                BuchungProtokoll::create([
                    'buchung_id' => $model->id,
                    'user_id' => Auth::id(),
                    'aktion' => 'updated',
                    'from_status' => $original['status'] ?? '',
                    'to_status' => $model->status,
                    'details' => json_encode($model->getDirty()),
                ]);
            }
        });
    }

    public function markt()
    {
        return $this->belongsTo(Markt::class);
    }

    // Relation für die Termin-Objekte
    public function termineObjekte()
    {
        $terminIds = $this->getRawOriginal('termine');
        if ($terminIds) {
            $terminIds = is_array($terminIds) ? $terminIds : json_decode($terminIds, true);
            return Termin::whereIn('id', $terminIds)->get();
        }
        return collect();
    }

    public function standort()
    {
        return $this->belongsTo(Standort::class);
    }

    public function aussteller()
    {
        return $this->belongsTo(Aussteller::class);
    }

    public function leistungen()
    {
        return $this->hasMany(BuchungLeistung::class);
    }

    public function protokolle()
    {
        return $this->hasMany(\App\Models\BuchungProtokoll::class);
    }

    public function rechnungen()
    {
        return $this->hasMany(Rechnung::class);
    }

    public function aktuelleRechnung()
    {
        return $this->hasOne(Rechnung::class)
            ->where('status', '!=', 'canceled')
            ->latest();
    }

    // Helper für Rechnungserstellung
    public function hatAktiveRechnung(): bool
    {
        return $this->aktuelleRechnung()->exists();
    }

    public function hatRechnungen(): bool
    {
        return $this->rechnungen()->exists();
    }
}
