<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medien extends Model
{
    protected $table = 'medien';
    
    protected $fillable = [
        'mediable_type',
        'mediable_id', 
        'category',
        'title',
        'description',
        'mime_type',
        'file_extension',
        'path',
        'size',
        'metadata',
        'sort_order',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Polymorphic Relation zu allen Modellen die Medien haben können
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Scope für bestimmte Kategorien
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope für Bilder
     */
    public function scopeImages($query)
    {
        return $query->whereIn('category', ['angebot', 'stand', 'werkstatt']);
    }

    /**
     * Scope für Dateien
     */
    public function scopeFiles($query)
    {
        return $query->where('category', 'vita');
    }

    /**
     * Hilfsmethode um zu prüfen ob es ein Bild ist
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Hilfsmethode um die volle URL zu bekommen
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Hilfsmethode um menschenlesbare Dateigröße zu bekommen
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) return 'Unbekannt';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
