<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppSetting extends Model
{
    /** @use HasFactory<\Database\Factories\AppSettingFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app_settings';

    /**
     * Disable timestamps for this model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'web_name',
        'web_header',
        'app_logo_uri',
        'notice',
    ];

    /** Get the setting data that on the database, based on what user asked for. */
    public static function getSetting($key)
    {
        $setting = self::first();
        $query = 'web_'.Str::lower($key);

        return $setting ? $setting->$query : null;
    }

    public static function updateSetting($key, $value): bool
    {
        $setting = self::first();
        $query = 'web_'.Str::lower($key);

        if ($setting) {
            $setting->$query = $value;
            $setting->save();

            return true;
        }

        return false;
    }

    public static function getNotice()
    {
        $setting = self::first();

        return $setting ? $setting->notice : null;
    }

    public static function updateNotice($value): bool
    {
        $setting = self::first();

        if ($setting) {
            $setting->notice = $value;
            $setting->save();

            return true;
        }

        return false;
    }
}
