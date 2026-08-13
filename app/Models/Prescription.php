<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;
    protected $fillable = [
        'image_path',
        // 'upload_date',
        'notes',
        'status'
    ];

    protected $casts = [
        'upload_date' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
