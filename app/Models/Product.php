<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes (Model layer - Lab 5).
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'quantity',
        'reorder_level',
        'unit_price',
        'supplier',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * One product has many stock movement records.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->latest();
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    /**
     * Business rule: at or below the reorder level means the item needs restocking.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function getStockStatusAttribute(): string
    {
        if ((int) $this->quantity === 0) {
            return 'Out of Stock';
        }

        return $this->isLowStock() ? 'Low Stock' : 'In Stock';
    }

    public function getStockValueAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    /**
     * Gym inventory categories - continuation of the Lab 4 JimTrack Fitness theme.
     */
    public static function categories(): array
    {
        return ['Equipment', 'Supplements', 'Apparel', 'Accessories', 'Nutrition', 'Recovery'];
    }
}
