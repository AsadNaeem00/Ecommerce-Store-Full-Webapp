<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_code', 'name', 'slug', 'description',
        'image', 'parent_id', 'sort_order', 'is_active', 'show_in_nav',
    ];

    protected $casts = ['is_active' => 'boolean', 'show_in_nav' => 'boolean'];

    // Relations
    public function products(): HasMany  { return $this->hasMany(Product::class); }
    public function children(): HasMany  { return $this->hasMany(Category::class, 'parent_id'); }
    public function parent(): BelongsTo  { return $this->belongsTo(Category::class, 'parent_id'); }

    // Scopes
    public function scopeActive($query)    { return $query->where('is_active', true); }
    public function scopeTopLevel($query)  { return $query->whereNull('parent_id'); }

    // Auto-generate slug and category_code
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            if (empty($category->category_code)) {
                // Generate unique 4-digit code
                do {
                    $code = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                } while (static::where('category_code', $code)->exists());
                $category->category_code = $code;
            }
        });
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/category-placeholder.png');
    }
}
