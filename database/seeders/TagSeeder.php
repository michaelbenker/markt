<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Positive Tags (grün)
            [
                'name' => 'Schöner Stand',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            [
                'name' => 'Außergewöhnliches Sortiment',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            [
                'name' => 'Für alle Märkte geeignet',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            [
                'name' => 'Zuverlässig',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            [
                'name' => 'Pünktlich',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            [
                'name' => 'Beliebter Aussteller',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            [
                'name' => 'Gute Qualität',
                'color' => '#10b981',
                'type' => 'positiv',
            ],
            
            // Neutrale Tags (grau)
            [
                'name' => 'Stammaussteller',
                'color' => '#6b7280',
                'type' => 'neutral',
            ],
            [
                'name' => 'Neu',
                'color' => '#6b7280',
                'type' => 'neutral',
            ],
            [
                'name' => 'Benötigt Strom',
                'color' => '#6b7280',
                'type' => 'neutral',
            ],
            [
                'name' => 'Benötigt viel Platz',
                'color' => '#6b7280',
                'type' => 'neutral',
            ],
            [
                'name' => 'Spezielles Sortiment',
                'color' => '#6b7280',
                'type' => 'neutral',
            ],
            
            // Negative Tags (rot)
            [
                'name' => 'Kompliziert',
                'color' => '#ef4444',
                'type' => 'negativ',
            ],
            [
                'name' => 'Unzuverlässig',
                'color' => '#ef4444',
                'type' => 'negativ',
            ],
            [
                'name' => 'Unpünktlich',
                'color' => '#ef4444',
                'type' => 'negativ',
            ],
            [
                'name' => 'Beschwerden',
                'color' => '#ef4444',
                'type' => 'negativ',
            ],
            [
                'name' => 'Vorsicht',
                'color' => '#ef4444',
                'type' => 'negativ',
            ],
            [
                'name' => 'Nicht mehr einladen',
                'color' => '#ef4444',
                'type' => 'negativ',
            ],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
