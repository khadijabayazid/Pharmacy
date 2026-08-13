<?php

namespace App\Models;

use App\Enums\ProductDetailType;
use Illuminate\Database\Eloquent\Model;

class Productdetail extends Model
{
    protected $fillable = ['product_id', 'type' ,'content'];

    protected function casts()
    {
        return[
            'type' => ProductDetailType::class,
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
