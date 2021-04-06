<?php

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::macro(
    'isToMany',
    function (): bool {
        return is_a($this, HasMany::class) || is_a($this, BelongsToMany::class)
            || is_a($this, MorphMany::class) || is_a($this, MorphToMany::class)
            || is_a($this, HasManyThrough::class);
    }
);
