<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_number',
        'location',
        'status',
        'responsible_id',
        'start_date',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inventory) {
            if (! $inventory->inventory_number) {
                $inventory->inventory_number = self::generateInventoryNumber();
            }
            if (! $inventory->start_date) {
                $inventory->start_date = now();
            }
        });
    }

    public static function generateInventoryNumber(): string
    {
        $year = now()->year;
        $lastInventory = self::whereYear('created_at', $year)->latest('id')->first();
        $number = $lastInventory ? (int) substr($lastInventory->inventory_number, -4) + 1 : 1;

        return sprintf('INV-%d-%04d', $year, $number);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function complete(): void
    {
        $this->status = 'concluido';
        $this->end_date = now();
        $this->save();
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'concluido');
    }
}
