<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'booking';
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
        'booking_id',
        'booking_name',
        'room_id',
        'booking_time',
        'user_id',
        'booked_from',
        'booked_to',
        'attendees',
        'approval_status',
        'approval_person_id',
        'approval_time',
        'approval_comment',
        'checking_status',
        'checking_person_id',
        'checking_time',
        'checkout_time',
        'checkout_person_id',
        'booking_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_time' => 'datetime',
        'booked_from' => 'datetime',
        'booked_to' => 'datetime',
        'approval_time' => 'datetime',
        'attendees' => 'array',
    ];

    /**
     * Boot the model and generate UUID for booking_id.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->booking_id)) {
                $model->booking_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns this booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved this booking.
     */
    public function approvalPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_person_id');
    }

    /**
     * Get the room associated with this booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public static function getCurrentUserBookings($userId, $status = null)
    {
        return self::where('user_id', $userId)
            ->orderBy('booking_time', 'desc')
            ->when($status, function ($query, $status) {
                return $query->where('approval_status', $status);
            })
            ->get();
    }

    public static function countCurrentUserBookings($userId, $status = null)
    {
        return self::where('user_id', $userId)
            ->when($status, function ($query, $status) {
                return $query->where('approval_status', $status);
            })
            ->count();
    }

    public static function getBookingByID($bookingId)
    {
        return self::where('booking_id', $bookingId)->get();
    }

    public static function getAllBookings($status = null, $quantity = null)
    {
        return self::orderBy('booking_time', 'desc')
            ->when($status, function ($query, $status) {
                return $query->where('approval_status', $status);
            })
            ->when($quantity, function ($query, $quantity) {
                return $query->limit($quantity);
            })
            ->get();
    }

    public static function countAllBookings($status = null, $quantity = null)
    {
        return self::when($status, function ($query, $status) {
            return $query->where('approval_status', $status);
        })
            ->when($quantity, function ($query, $quantity) {
                return $query->limit($quantity);
            })
            ->count();
    }

    public static function bookingStatusToText($status)
    {
        return match ($status) {
            'waiting' => 'รอการอนุมัติ',
            'approved' => 'อนุมัติแล้ว',
            'rejected' => 'ถูกปฏิเสธ',
            default => 'ไม่ทราบ',
        };
    }

    public static function approveBooking(Request $request, $bookingId)
    {
        $booking = self::where('booking_id', $bookingId)->first();
        if ($request->input('action') === 'approve') {
            $booking->approval_status = 'approved';
            $booking->booking_status = 'approved';
        } elseif ($request->input('action') === 'reject') {
            $booking->approval_status = 'rejected';
            $booking->booking_status = 'rejected';
        } else {
            throw new \InvalidArgumentException('Invalid action for booking approval.');
        }
        $booking->approval_person_id = Auth::id();
        $booking->approval_time = now();
        $booking->approval_comment = $request->input('approval_comment', null);
        $booking->save();
    }

    public static function delBooking($bookingId)
    {
        $booking = self::where('booking_id', $bookingId)->first();
        if ($booking) {
            $booking->delete();
            return true;
        }
        return false;
    }

    public static function parseAttendeeforDisplay($attendee)
    {
        if (is_string($attendee)) {
            $attendee = json_decode($attendee, true);
        }

        if (empty($attendee) || !is_array($attendee) || !isset($attendee['attendee'])) {
            return [];
        }

        $attendeeList = [];
        foreach ($attendee['attendee'] as $user) {
            $name = $user['user_name'] ?? 'Unknown';
            if (isset($user['user_from']) && $user['user_from'] === 'id' && isset($user['user_identify'])) {
                $dbUser = User::where('student_id', $user['user_identify'])
                    ->orWhere('id', $user['user_identify'])
                    ->first();
                if ($dbUser) {
                    $name = $dbUser->name . ' ' . $dbUser->surname;
                }
            }
            $identify = $user['user_identify'] ?? '-';
            $attendeeList[] = $name . ' (' . $identify . ')';
        }
        return $attendeeList;
    }

    public static function parseAttendeeforName($attendee)
    {
        if (is_string($attendee)) {
            $attendee = json_decode($attendee, true);
        }

        if (empty($attendee) || !is_array($attendee) || !isset($attendee['attendee'])) {
            return [];
        }

        $attendeeList = [];
        foreach ($attendee['attendee'] as $user) {
            $name = $user['user_name'] ?? 'Unknown';
            if (isset($user['user_from']) && $user['user_from'] === 'id' && isset($user['user_identify'])) {
                $dbUser = User::where('student_id', $user['user_identify'])
                    ->orWhere('id', $user['user_identify'])
                    ->first();
                if ($dbUser) {
                    $name = $dbUser->name . ' ' . $dbUser->surname;
                }
            }
            $attendeeList[] = $name;
        }
        return $attendeeList;
    }
}
