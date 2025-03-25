<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        Log::info('Getting setting:', [
            'key' => $key,
            'found' => $setting ? true : false,
            'value' => $setting ? $setting->value : $default
        ]);

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }

    public static function set($key, $value, $type = 'string', $group = 'general')
    {
        Log::info('Setting value:', [
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'group' => $group
        ]);

        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->type = $type;
        $setting->group = $group;
        $setting->save();

        Log::info('Setting saved:', ['setting' => $setting->toArray()]);

        return $setting;
    }
}
