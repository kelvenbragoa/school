<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'item_id',
        'reference',
        'work_order',
        'quantity',
        'equipment_id',
        'replacement_reason',
        'status',
        'requester_id',
        'notes',
        'requested_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (! $request->request_number) {
                $request->request_number = self::generateRequestNumber();
            }
            if (! $request->requested_at) {
                $request->requested_at = now();
            }
        });
    }

    public static function generateRequestNumber(): string
    {
        $year = now()->year;
        $lastRequest = self::whereYear('created_at', $year)->latest('id')->first();
        $number = $lastRequest ? (int) substr($lastRequest->request_number, -4) + 1 : 1;

        return sprintf('REQ-%d-%04d', $year, $number);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class)->orderBy('changed_at', 'desc');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function canEdit(): bool
    {
        return $this->status === 'solicitado';
    }

    public function canCancel(): bool
    {
        return $this->status === 'solicitado';
    }

    public function changeStatus(string $newStatus, User $user, ?string $notes = null): void
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;

        if ($newStatus === 'entregue') {
            $this->completed_at = now();
        }

        $this->save();

        $this->statusHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => $user->id,
            'notes' => $notes,
            'changed_at' => now(),
        ]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRequester($query, $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('request_number', 'like', "%{$search}%")
                ->orWhere('work_order', 'like', "%{$search}%")
                ->orWhere('equipment_id', 'like', "%{$search}%");
        });
    }
}
