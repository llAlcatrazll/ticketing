<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void {}

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withDeactivated', function (Builder $builder) {
            return $builder->withoutGlobalScope(static::class);
        });

        $builder->macro('withoutDeactivated', function (Builder $builder) {
            return $builder->withoutGlobalScope(static::class)->whereNull($builder->getModel()->getTable().'.deactivated_at');
        });

        $builder->macro('onlyDeactivated', function (Builder $builder) {
            return $builder->withoutGlobalScope(static::class)->whereNotNull($builder->getModel()->getTable().'.deactivated_at');
        });
    }
}
