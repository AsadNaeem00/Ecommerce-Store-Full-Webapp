<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'shipping_city', 'shipping_province', 'shipping_postal_code',
        'subtotal', 'shipping_cost', 'discount_amount', 'total_amount',
        'status', 'payment_method', 'payment_status', 'payment_reference',
        'admin_notes', 'customer_notes', 'coupon_code', 'ip_address',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'shipping_cost'   => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    // Status labels
    public static array $statusLabels = [
        'pending'    => 'Pending',
        'confirmed'  => 'Confirmed',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
        'refunded'   => 'Refunded',
    ];

    public static array $statusColors = [
        'pending'    => 'yellow',
        'confirmed'  => 'blue',
        'processing' => 'indigo',
        'shipped'    => 'purple',
        'delivered'  => 'green',
        'cancelled'  => 'red',
        'refunded'   => 'gray',
    ];

    // Relations
    public function items(): HasMany         { return $this->hasMany(OrderItem::class); }
    public function statusHistory(): HasMany { return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at'); }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }
}
