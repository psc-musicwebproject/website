<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'username', 
        'student_id',
        'type',
        'class',
        'password',
        'line_id',
        'line_bound'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the club membership for this user.
     */
    public function clubMembership()
    {
        return $this->hasOne(ClubMember::class, 'user_id');
    }

    /**
     * Get the approved club memberships for this user.
     */
    public function approvedClubMemberships()
    {
        return $this->hasMany(ClubMember::class, 'approval_person_id');
    }

    /**
     * Check if user is a club member.
     */
    public function isClubMember(): bool
    {
        return $this->clubMembership()->where('status', 'approved')->exists();
    }

    /**
     * Check if user has pending club application.
     */
    public function hasPendingClubApplication(): bool
    {
        return $this->clubMembership()->where('status', 'waiting')->exists();
    }
}
