<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rooms';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'room_uuid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Disable timestamps for this model.
     * @var bool
     */
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_uuid',
        'room_name',
        'room_status',
    ];
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'room_status' => 'available',
    ];

    /**
     * Boot the model and generate UUID for room_uuid.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->room_uuid)) {
                $model->room_uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the bookings for the room.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'room_id', 'room_uuid');
    }

    public static function add(string $name)
    {
        $newRoom = new self();
        $newRoom->room_name = $name;
        $newRoom->save();
    }

    public static function edit(string $room_uuid, $config_type , $value)
    {
        $room = self::where('room_uuid', $room_uuid)->first();
        if ($room) {
            $query = 'room_'.$config_type;
            $room->$query = $value;
            $room->save();
        }
    }

    public static function getAllRooms()
    {
        return self::all();
    }

    public static function del(string $room_uuid)
    {
        $room = self::where('room_uuid', $room_uuid)->first();
        if ($room) {
            $room->delete();
        }
    }

    public static function disable(string $room_uuid)
    {
        $room = self::where('room_uuid', $room_uuid)->first();
        if ($room) {
            $room->room_status = 'disabled';
            $room->save();
        }
    }

    public static function enable(string $room_uuid)
    {
        $room = self::where('room_uuid', $room_uuid)->first();
        if ($room) {
            $room->room_status = 'available';
            $room->save();
        }
    }

    public static function getAvailableRooms()
    {
        /** Only room that don't get disabled, in_use doesn't count,
         * since we can write the function to get date-time range for to exclude that out. */
        return self::where('room_status', '!=', 'disabled')->get();
    }

    public static function getRoomByID($roomId)
    {
        return self::where('room_uuid', $roomId)->first();
    }

    public static function getRoomNameByID($roomId)
    {
        $room = self::where('room_uuid', $roomId)->first();
        return $room ? $room->room_name : null;
    }
}
