<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model {
    protected $fillable = ['product_id','reviewer_name','reviewer_email','rating','title','body','status','is_verified_purchase','ip_address'];
    protected $casts    = ['rating'=>'integer','is_verified_purchase'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function scopeApproved($q) { return $q->where('status','approved'); }
    public function scopePending($q)  { return $q->where('status','pending'); }
}
