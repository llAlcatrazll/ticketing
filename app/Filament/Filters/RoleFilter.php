<?php

namespace App\Filament\Filters;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RoleFilter extends Filter
{
    public static function make(?string $name = null): static
    {
        $filterClass = static::class;

        $name ??= 'role-filter';

        $static = app($filterClass, ['name' => $name]);

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $roles = collect(UserRole::cases())
            ->reject(fn ($role) => $role === UserRole::ROOT)
            ->mapWithKeys(fn ($role) => [$role->value => $role->getLabel()])
            ->prepend('Undesignated', -1);

        $this->form([
            Select::make('role')
                ->label('Designated role')
                ->options($roles->toArray())
                ->placeholder('With undesignated role'),
        ]);

        $this->query(function (Builder $query, array $data) {
            switch (is_array($data['role'])) {
                case true:
                    $role = collect($data['role'])->map(fn ($role) => (int) $role !== -1 ? $role : null)->toArray();

                    $query->when($role, fn ($query, $role) => $query->whereIn('role', $role));

                    break;

                case false:
                    $role = $data['role'];

                    $query->when($role, fn ($query, $role) => $query->where('role', $role !== -1 ? $role : null));

                    break;
            }
        });

        $this->indicateUsing(function (array $data) {
            if (empty($data['role'])) {
                return;
            }

            $roles = is_array($data['role']) ? $data['role'] : [$data['role']];

            $roles = collect($roles)
                ->map(fn ($role) => UserRole::tryFrom($role)?->getLabel() ?? 'Undesignated');

            return 'Role: '.$roles->join(', ', ', & ');
        });
    }
}
