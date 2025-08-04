<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Markt extends Model
{
    use HasFactory;
    protected $table = 'markt';
    protected $fillable = [
        'slug',
        'name',
        'bemerkung',
        'url',
        'subkategorien',
    ];

    protected $casts = [
        'subkategorien' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function standorte()
    {
        return $this->belongsToMany(Standort::class, 'markt_standort');
    }

    public function termine()
    {
        return $this->hasMany(Termin::class);
    }

    public function getSubkategorienObjectsAttribute()
    {
        if (!$this->subkategorien) {
            return collect();
        }
        
        return Subkategorie::whereIn('id', $this->subkategorien)->get();
    }
}
