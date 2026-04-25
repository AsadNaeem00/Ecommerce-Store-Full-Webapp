<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model {
    protected $fillable = ['key','value','type','group','label'];

    public static function get(string $key, $default = null) {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $s = static::where('key', $key)->first();
            return $s ? $s->value : $default;
        });
    }

    public static function set(string $key, $value): void {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'updated_at' => now()]);
        Cache::forget("setting_{$key}");
    }

    public static function getGroup(string $group): array {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    public static function setMany(array $data): void {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }
}
