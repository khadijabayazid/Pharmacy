<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    private const LOW_STOCK_THRESHOLD = 10;
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'category_id',
        'is_required_prescription',
        'image_path'
    ];

    protected $casts = [
        'is_required_prescription' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url', 'stock_status', 'stock_status_label'];

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getStockStatusAttribute(): string
    {
        return $this->resolveStockStatus()->value;
    }

    public function getStockStatusLabelAttribute(): string
    {
        return $this->resolveStockStatus()->label();
    }

    private function resolveStockStatus(): StockStatus
    {
        if ($this->quantity <= 0) {
            return StockStatus::OutOfStock;
        }

        if ($this->quantity <= self::LOW_STOCK_THRESHOLD) {
            return StockStatus::LowStock;
        }

        return StockStatus::InStock;
    }

    public function details()
    {
        return $this->hasMany(ProductDetail::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->wherePivot('quantity', 'price')
            ->withTimestamps();
    }
}
