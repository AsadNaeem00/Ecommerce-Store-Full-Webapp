<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'slug', 'category_id', 'short_description', 'description',
        'price', 'sale_price', 'cost_price', 'stock_quantity', 'low_stock_threshold',
        'main_image', 'weight', 'dimensions', 'is_active', 'is_featured',
        'track_quantity', 'allow_backorder', 'tags', 'meta', 'created_by',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'sale_price'      => 'decimal:2',
        'cost_price'      => 'decimal:2',
        'is_active'       => 'boolean',
        'is_featured'     => 'boolean',
        'track_quantity'  => 'boolean',
        'allow_backorder' => 'boolean',
        'tags'            => 'array',
        'meta'            => 'array',
    ];

    // Relations
    public function category(): BelongsTo  { return $this->belongsTo(Category::class); }
    public function images(): HasMany      { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function reviews(): HasMany     { return $this->hasMany(Review::class); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }

    // Scopes
    public function scopeActive($query)   { return $query->where('is_active', true); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }

    // Computed
    public function getCurrentPriceAttribute(): float
    {
        return $this->sale_price && $this->sale_price < $this->price
            ? (float) $this->sale_price
            : (float) $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if ($this->sale_price && $this->sale_price < $this->price) {
            return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return null;
    }

    public function getMainImageUrlAttribute(): string
    {
        return $this->main_image
            ? asset('storage/' . $this->main_image)
            : asset('images/product-placeholder.png');
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->where('status', 'approved')->avg('rating') ?? 0, 1);
    }

    public function isInStock(): bool
    {
        return !$this->track_quantity || $this->stock_quantity > 0 || $this->allow_backorder;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $year  = date('Y');
                $count = static::withTrashed()->count() + 1;
                $product->sku = 'PRD-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
