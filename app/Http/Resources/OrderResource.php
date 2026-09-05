<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'address' => $this->address,
            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,
            'assigned_at' => $this->assigned_at,
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,

            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ]),

            'delivery' => $this->whenLoaded('delivery', fn() => $this->delivery ? [
                'id' => $this->delivery->id,
                'name' => $this->delivery->user->name,
                'phone' => $this->delivery->user->phone,
            ] : null),

            'prescription' => $this->whenLoaded('prescription', fn() => $this->prescription ? [
                'id' => $this->prescription->id,
                'image_url' => $this->prescription->image_url,
            ] : null),

            'items' => $this->whenLoaded('orderItems', fn() => $this->orderItems->map(fn($item) => [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ])),
        ];
    }
}
