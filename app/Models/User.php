<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relacionamentos
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'requester_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'responsible_id');
    }

    public function inventoryItemsCounted(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'counted_by');
    }

    /**
     * Helper methods para permissões
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor' || $this->isAdmin();
    }

    public function isArmazem(): bool
    {
        return $this->role === 'armazem' || $this->isGestor();
    }

    public function isSolicitante(): bool
    {
        return $this->role === 'solicitante';
    }

    public function canManageStock(): bool
    {
        return in_array($this->role, ['armazem', 'gestor', 'admin']);
    }

    public function canChangeRequestStatus(): bool
    {
        return in_array($this->role, ['armazem', 'gestor', 'admin']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
