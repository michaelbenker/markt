<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'color',
        'type',
    ];

    /**
     * Die Aussteller, die diesen Tag haben
     */
    public function aussteller()
    {
        return $this->belongsToMany(Aussteller::class, 'aussteller_tag')
            ->withPivot('notiz')
            ->withTimestamps();
    }

    /**
     * Scope für positive Tags
     */
    public function scopePositiv($query)
    {
        return $query->where('type', 'positiv');
    }

    /**
     * Scope für negative Tags
     */
    public function scopeNegativ($query)
    {
        return $query->where('type', 'negativ');
    }

    /**
     * Scope für neutrale Tags
     */
    public function scopeNeutral($query)
    {
        return $query->where('type', 'neutral');
    }
}