<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'inventory_id',
        'item_id',
        'system_stock',
        'counted_stock',
        'difference',
        'adjusted',
        'counted_by',
        'counted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_stock' => 'decimal:2',
            'counted_stock' => 'decimal:2',
            'difference' => 'decimal:2',
            'adjusted' => 'boolean',
            'counted_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventoryItem) {
            if ($inventoryItem->counted_stock !== null) {
                $inventoryItem->difference = $inventoryItem->counted_stock - $inventoryItem->system_stock;
            }
        });
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function hasDifference(): bool
    {
        return $this->difference != 0;
    }

    public function scopeWithDifferences($query)
    {
        return $query->whereNotNull('difference')
            ->where('difference', '!=', 0);
    }

    public function scopeNotAdjusted($query)
    {
        return $query->where('adjusted', false);
    }
}
