<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const DELIVERY_PRICE = 100;

    use HasFactory;
    protected $fillable = [
        'user_id',
        'delivery_id',
        'prescription_id',
        'status',
        'rejection_reason',
        'address',
        'total_price',
        'delivery_price',
        'assigned_at',
        'delivered_at',
        'pharmacy_rating',
        'delivery_rating',
        'review_comments'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'delivered_at' => 'datetime',
        'total_price' => 'decimal:2',
        'delivery_price' => 'decimal:2',
        'pharmacy_rating' => 'integer',
        'delivery_rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
