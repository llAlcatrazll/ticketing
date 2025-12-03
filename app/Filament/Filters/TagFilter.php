<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;

class TagFilter extends SelectFilter
{
    public static function make(?string $name = null): static
    {
        $filterClass = static::class;

        $name ??= 'tag-filter';

        $static = app($filterClass, ['name' => $name]);

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('tags');

        $this->relationship('tags', 'name')
            ->multiple()
            ->preload()
            ->placeholder('Select tags');
    }
}
