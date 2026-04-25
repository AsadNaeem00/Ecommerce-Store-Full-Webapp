<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SliderImage extends Model {
    protected $fillable = ['image_path','title','subtitle','cta_text','cta_url','sort_order','is_active'];
    protected $casts    = ['is_active'=>'boolean'];
    public function scopeActive($q) { return $q->where('is_active',true)->orderBy('sort_order'); }
    public function getImageUrlAttribute(): string { return asset('storage/'.$this->image_path); }
}

class HomepageSection extends Model {
    protected $fillable = ['section_key','title','subtitle','is_enabled','sort_order','config'];
    protected $casts    = ['is_enabled'=>'boolean','config'=>'array'];
    public function scopeEnabled($q) { return $q->where('is_enabled',true)->orderBy('sort_order'); }
}

class Page extends Model {
    protected $fillable = ['slug','title','content','meta_title','meta_description','is_active'];
    protected $casts    = ['is_active'=>'boolean'];
    public static function findBySlug(string $slug): ?self {
        return static::where('slug',$slug)->where('is_active',true)->first();
    }
}
