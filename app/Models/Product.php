<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuid, HasPublicId, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'image',
        'low_stock_threshold',
        'is_active',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['formatted_public_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the cart items for the product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if the product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Check if the product has enough stock.
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Decrease the stock quantity.
     */
    public function decreaseStock(int $quantity): void
    {
        $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Increase the stock quantity.
     */
    public function increaseStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }
}
