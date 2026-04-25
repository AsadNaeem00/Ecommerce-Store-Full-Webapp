<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentConfig extends Model {
    protected $fillable = ['gateway','is_enabled','is_test_mode','merchant_id','api_key','api_secret','extra_config','webhook_secret'];
    protected $casts    = ['is_enabled'=>'boolean','is_test_mode'=>'boolean','extra_config'=>'array'];

    public static function forGateway(string $gateway): ?self {
        return static::where('gateway', $gateway)->first();
    }

    public function setApiKeyAttribute($value): void {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getApiKeyAttribute($value): ?string {
        try { return $value ? Crypt::decryptString($value) : null; } catch (\Exception $e) { return null; }
    }
    public function setApiSecretAttribute($value): void {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getApiSecretAttribute($value): ?string {
        try { return $value ? Crypt::decryptString($value) : null; } catch (\Exception $e) { return null; }
    }
}
