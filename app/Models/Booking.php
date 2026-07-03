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
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'booking_uuid';

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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_uuid',
        'booking_name',
        'room_id',
        'booking_time',
        'user_id',
        'booked_from',
        'booked_to',
        'attendees',
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
            if (empty($model->booking_uuid)) {
                $model->booking_uuid = (string) Str::uuid();
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
        return $this->belongsTo(Room::class, 'room_id', 'room_uuid');
    }

    public static function getCurrentUserBookings($userUUID, $status = null)
    {
        $user = User::where('id', $userUUID)->orWhere('user_id', $userUUID)->first();

        return self::where(function ($query) use ($userUUID, $user) {
            $query->where('user_id', $userUUID);
            if ($user) {
                $query->orWhere('attendees', 'like', "%\"user_identify\":\"{$user->id}\"%")
                      ->orWhere('attendees', 'like', "%\"user_identify\":\"{$user->user_id}\"%")
                      ->orWhere('attendees', 'like', "%\"user_identify\":\"{$user->email}\"%");
            } else {
                $query->orWhere('attendees', 'like', "%\"user_identify\":\"{$userUUID}\"%");
            }
        })
        ->orderBy('booking_time', 'desc')
        ->when($status, function ($query, $status) {
            return $query->where('booking_status', $status);
        })
        ->get();
    }

    public static function countCurrentUserBookings($userUUID, $status = null)
    {
        $user = User::where('id', $userUUID)->orWhere('user_id', $userUUID)->first();

        return self::where(function ($query) use ($userUUID, $user) {
            $query->where('user_id', $userUUID);
            if ($user) {
                $query->orWhere('attendees', 'like', "%\"user_identify\":\"{$user->id}\"%")
                      ->orWhere('attendees', 'like', "%\"user_identify\":\"{$user->user_id}\"%")
                      ->orWhere('attendees', 'like', "%\"user_identify\":\"{$user->email}\"%");
            } else {
                $query->orWhere('attendees', 'like', "%\"user_identify\":\"{$userUUID}\"%");
            }
        })
        ->when($status, function ($query, $status) {
            return $query->where('booking_status', $status);
        })
        ->count();
    }

    public static function getBookingByUUID($bookingUUID)
    {
        return self::where('booking_uuid', $bookingUUID)->get();
    }

    public static function getAllBookings($status = null, $quantity = null)
    {
        return self::orderBy('booking_time', 'desc')
            ->when($status, function ($query, $status) {
                return $query->where('booking_status', $status);
            })
            ->when($quantity, function ($query, $quantity) {
                return $query->limit($quantity);
            })
            ->get();
    }

    public static function countAllBookings($status = null, $quantity = null)
    {
        return self::when($status, function ($query, $status) {
            return $query->where('booking_status', $status);
        })
            ->when($quantity, function ($query, $quantity) {
                return $query->limit($quantity);
            })
            ->count();
    }

    public static function bookingStatusToText($status)
    {
        return match ($status) {
            'waiting_approval' => 'รอการอนุมัติ',
            'approved' => 'อนุมัติแล้ว',
            'rejected' => 'ถูกปฏิเสธ',
            default => 'ไม่ทราบ',
        };
    }

    public static function approveBooking(Request $request, $bookingUUID)
    {
        $booking = self::where('booking_uuid', $bookingUUID)->first();
        if ($request->input('action') === 'approve') {
            $booking->booking_status = 'approved';
        } elseif ($request->input('action') === 'reject') {
            $booking->booking_status = 'rejected';
        } else {
            throw new \InvalidArgumentException('Invalid action for booking approval.');
        }
        $booking->approval_person_id = Auth::id();
        $booking->approval_time = now();
        $booking->approval_comment = $request->input('approval_comment', null);
        $booking->save();
    }

    public static function delBooking($bookingUUID)
    {
        $booking = self::where('booking_uuid', $bookingUUID)->first();
        if ($booking) {
            $booking->delete();
            return true;
        }
        return false;
    }

    public function parseAttendeeforDisplay()
    {
        $attendees = $this->attendees;

        if (empty($attendees) || !is_array($attendees) || !isset($attendees['attendee'])) {
            return [];
        }
        if (is_string($attendees)) {
            $attendees = json_decode($attendees, true);
        }

        $attendeeList = [];
        if ($this->user) {
            $attendeeList[] = $this->user->name . ' ' . $this->user->surname . ' (' . ($this->user->user_id ?? '-') . ')';
        }

        foreach ($attendees['attendee'] as $user) {
            $name = $user['user_name'] ?? 'Unknown';
            if (isset($user['user_from']) && $user['user_from'] === 'id' && isset($user['user_identify'])) {
                $dbUser = User::where('user_id', $user['user_identify'])
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
                $dbUser = User::where('user_id', $user['user_identify'])
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
    public function isAttendee(User $user): bool
    {
        $attendees = $this->attendees;
        if (is_string($attendees)) {
            $attendees = json_decode($attendees, true);
        }

        if (empty($attendees) || !is_array($attendees)) {
            return false;
        }

        // Try to handle both structure layouts if needed, but primarily the one in history
        /*
         { "attendee": [ ... ] }
        */
        $list = $attendees['attendee'] ?? $attendees;
        if (!is_array($list)) return false;

        foreach ($list as $p) {
            // Check user_id or id
            if (isset($p['user_from']) && $p['user_from'] === 'id') {
                if (isset($p['user_identify'])) {
                    if ($p['user_identify'] == $user->user_id || $p['user_identify'] == $user->id) {
                        return true;
                    }
                }
            }
            // Check email
            if (isset($p['user_from']) && $p['user_from'] === 'mail') {
                if (isset($p['user_identify']) && $p['user_identify'] == $user->email) {
                    return true;
                }
            }
        }

        return false;
    }

    public function fetchInternalAttendeeID($attendee) {
        if (is_string($attendee)) {
            $attendees = json_decode($attendee, true);
            $IntAttendID = [];
            if (empty($attendees) || !is_array($attendees) || !isset($attendees['attendee'])) {
                return $IntAttendID;
            } else {
                foreach ($attendees['attendee'] as $user) {
                    if (isset($user['user_from']) && $user['user_from'] === 'id' && isset($user['user_identify'])) {
                        $dbUser = User::where('user_id', $user['user_identify'])
                            ->orWhere('id', $user['user_identify'])
                            ->first();
                        if ($dbUser) {
                            $IntAttendID[] = $dbUser->id;
                        }
                    }
                }
                return $IntAttendID;
            }
        }
    }

    public function fetchGuestMailList($attendee) {
        // Return array of guest emails from attendees
        if (is_string($attendee)) {
            $attendees = json_decode($attendee, true);
            $GuestEmailList = [];
            if (empty($attendees) || !is_array($attendees) || !isset($attendees['attendee'])) {
                return $GuestEmailList;
            } else {
                foreach ($attendees['attendee'] as $user) {
                    if (isset($user['user_from']) && $user['user_from'] === 'mail' && isset($user['user_identify'])) {
                        $GuestEmailList[] = $user['user_identify'];
                    }
                }
                return $GuestEmailList;
            }
        }
    }

    /**
     * Fetch internal attendees as User collection.
     * @param Booking $booking
     * @return \Illuminate\Support\Collection<User>
     */
    public static function fetchInternalAttendeeList(Booking $booking): \Illuminate\Support\Collection
    {
        $attendees = $booking->attendees;
        if (is_string($attendees)) {
            $attendees = json_decode($attendees, true);
        }

        $userIds = [];
        if (!empty($attendees) && is_array($attendees) && isset($attendees['attendee'])) {
            foreach ($attendees['attendee'] as $user) {
                if (isset($user['user_from']) && $user['user_from'] === 'id' && isset($user['user_identify'])) {
                    $dbUser = User::where('user_id', $user['user_identify'])
                        ->orWhere('id', $user['user_identify'])
                        ->first();
                    if ($dbUser) {
                        $userIds[] = $dbUser->id;
                    }
                }
            }
        }

        return User::whereIn('id', $userIds)->get();
    }

    /**
     * Fetch guest attendees with email and name.
     * @param Booking $booking
     * @return array<array{email: string, name: string}>
     */
    public static function fetchGuestAttendeeList(Booking $booking): array
    {
        $attendees = $booking->attendees;
        if (is_string($attendees)) {
            $attendees = json_decode($attendees, true);
        }

        $guestList = [];
        if (!empty($attendees) && is_array($attendees) && isset($attendees['attendee'])) {
            foreach ($attendees['attendee'] as $user) {
                if (isset($user['user_from']) && $user['user_from'] === 'mail' && isset($user['user_identify'])) {
                    $guestList[] = [
                        'email' => $user['user_identify'],
                        'name' => $user['user_name'] ?? 'Guest',
                    ];
                }
            }
        }

        return $guestList;
    }

    public function isOwnerOrAttendee(User $user): bool
    {
        return $this->user_id == $user->id || $this->isAttendee($user);
    }
}
