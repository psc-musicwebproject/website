<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClubMember extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'club_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'user_id',
        'status',
        'contact_info',
        'instrument',
        'experience',
        'wanted_duty',
        'approval_person_id',
        'approval_time',
        'approval_comment',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contact_info' => 'array',
        'instrument' => 'array',
        'experience' => 'array',
        'wanted_duty' => 'array',
        'approval_time' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'waiting',
    ];

    /**
     * Boot the model and generate UUID for member_id.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->member_id)) {
                $model->member_id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns this club membership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved this membership.
     */
    public function approvalPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_person_id');
    }

    /**
     * Scope a query to only include pending memberships.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope a query to only include approved memberships.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected memberships.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the membership is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if the membership is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the membership is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the membership.
     */
    public function approve(string $approvalPersonId, string $comment = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approval_person_id' => $approvalPersonId,
            'approval_time' => now(),
            'approval_comment' => $comment,
        ]);
    }

    /**
     * Reject the membership.
     */
    public function reject(string $approvalPersonId, string $comment = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approval_person_id' => $approvalPersonId,
            'approval_time' => now(),
            'approval_comment' => $comment,
        ]);
    }

    /** Get list of user that needed approval */
    public static function getPendingApprovals()
    {
        return self::with('user')
            ->pending()
            ->get();
    }

    public static function getApplicationByID(string $id)
    {
        return self::with('user')
            ->where('member_id', $id)
            ->get();
    }

    // Parse array attributes for display, from json
    // [{"type":"guitar","data":"checked"},{"type":"bass","data":"checked"}]
    // we won't show the checked text, just other data with type
    public static function parseAbilitiesForDisplay($ability) {
        if (empty($ability) || !is_array($ability)) {
            return '-';
        }

        $options = [
            'guitar'   => 'กีตาร์ (โปร่ง / ไฟฟ้า)',
            'bass'     => 'เบส',
            'drums'    => 'กลองชุด',
            'keyboard' => 'คีย์บอร์ด / เปียโน',
            'vocal'    => 'ร้องเพลง',
            'wind'     => 'เครื่องเป่าลม',
            'other'    => 'อื่นๆ',
        ];

        $display = [];

        foreach ($ability as $item) {
            $type = $item['type'] ?? '';
            $data = $item['data'] ?? '';

            if (array_key_exists($type, $options)) {
                $text = $options[$type];
                if (!empty($data) && $data !== 'checked') {
                    $text .= ' (' . $data . ')';
                }
                $display[] = $text;
            }
        }

        return empty($display) ? '-' : implode(', ', $display);
    }
}
