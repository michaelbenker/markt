<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'subject',
        'content',
        'description',
        'available_variables',
        'is_active',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Rendert das Template mit den gegebenen Variablen
     */
    public function render(array $variables = []): array
    {
        $subject = $this->renderString($this->subject, $variables);
        $content = $this->renderString($this->content, $variables);

        return [
            'subject' => $subject,
            'content' => $content,
        ];
    }

    /**
     * Ersetzt Platzhalter in einem String
     */
    private function renderString(string $template, array $variables): string
    {
        $rendered = $template;

        foreach ($variables as $key => $value) {
            // Unterstützt sowohl {{variable}} als auch {variable} Format
            $rendered = str_replace(['{{' . $key . '}}', '{' . $key . '}'], $value, $rendered);
        }

        return $rendered;
    }

    /**
     * Holt ein Template nach Key
     */
    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }
}
