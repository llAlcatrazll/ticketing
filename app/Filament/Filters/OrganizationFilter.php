<?php

namespace App\Filament\Filters;

use App\Models\Organization;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class OrganizationFilter extends Filter
{
    protected Closure|string|null $placeholder = null;

    protected bool $withUnaffiliated = true;

    public static function make(?string $name = null): static
    {
        $filterClass = static::class;

        $name ??= 'organization-filter';

        $static = app($filterClass, ['name' => $name]);

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->form(function () {
            $organizations = Organization::query()->pluck('code', 'id')->toArray();

            return [
                Select::make('organization')
                    ->label($this->evaluate($this->label))
                    ->options($this->withUnaffiliated ? [-1 => 'Unaffiliated', ...$organizations] : $organizations)
                    ->placeholder($this->placeholder ? $this->evaluate($this->placeholder) : ($this->withUnaffiliated ? 'With unaffiliated organization' : 'Select an organization'))
                    ->searchable(),
            ];
        });

        $this->query(function (Builder $query, array $data) {
            switch (is_array($data['organization'])) {
                case true:
                    $organizations = collect($data['organization'])->map(fn ($organization) => (int) $organization !== -1 ? $organization : null)->toArray();

                    $query->when($organizations, fn ($query, $organizations) => $query->whereIn('organization_id', $organizations));

                    break;

                case false:
                    $organization = $data['organization'];

                    $query->when($organization, fn ($query, $organization) => $query->where('organization_id', (int) $organization !== -1 ? $organization : null));

                    break;
            }
        });

        $this->indicateUsing(function (array $data) {
            if (empty($data['organization'])) {
                return;
            }

            $organizations = is_array($data['organization']) ? $data['organization'] : [$data['organization']];

            $organizations = Organization::select('code')
                ->orderBy('code')
                ->find($organizations)
                ->pluck('code')
                ->when(in_array(-1, $organizations), fn ($organizations) => $organizations->push('Unaffiliated'));

            return 'Organization: '.$organizations->join(', ', ', & ');
        });
    }

    public function placeholder(string|Closure $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function withUnaffiliated(bool $withUnaffiliated = true): static
    {
        $this->withUnaffiliated = $withUnaffiliated;

        return $this;
    }
}
