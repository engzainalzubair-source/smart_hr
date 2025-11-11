<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key','value','type','description'];

    public static function get($key, $default = null)
    {
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }

    public static function setMany(array $pairs)
    {
        foreach ($pairs as $k => $v) {
            static::updateOrCreate(['key' => $k], ['value' => $v]);
        }
    }
}
