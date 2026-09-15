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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bku_id',
    ];

    /**
     * Get the BKU associated with the user (for Operator BKU).
     */
    public function bku()
    {
        return $this->belongsTo(Bku::class, 'bku_id');
    }

    /**
     * Check if user is ESDM Staff.
     */
    public function isStafEsdm(): bool
    {
        return $this->role === 'staf_dinas' || $this->role === 'admin';
    }

    /**
     * Check if user is BKU Operator.
     */
    public function isOperatorBku(): bool
    {
        return $this->role === 'operator_bku';
    }

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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
