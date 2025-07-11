<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MarkdownEditorWithPreview extends Field
{
    protected string $view = 'forms.components.markdown-editor-with-preview';

    public static function make(string $name): static
    {
        return parent::make($name);
    }
}
