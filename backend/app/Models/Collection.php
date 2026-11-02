<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /* ---- Scopes: reutilizables en la API (Fase 1) ---- */

    /** Drops ya publicados (published_at en el pasado) */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** Drops futuros → "próximamente" con countdown */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '>', now());
    }

    /** ¿Es un drop que aún no sale? (is_upcoming del contrato) */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }
}
