<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'collection_id',
        'name',
        'slug',
        'description',
        'price',
        'status',
        'featured',
        'main_image',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', // el contrato manda precios como string "49.90"
            'featured' => 'boolean',
        ];
    }

    /* ---- Relaciones ---- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    /* ---- Scopes: reutilizables en la API (Fase 1) ---- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /* ---- Accessors ---- */

    /** has_stock del contrato: hay stock en alguna variante */
    public function getHasStockAttribute(): bool
    {
        return $this->variants()->sum('stock') > 0;
    }
}
