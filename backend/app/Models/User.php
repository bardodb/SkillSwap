<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'provider',
        'provider_id',
        'password',
        'bio',
        'location',
        'avatar',
        'phone',
        'rating',
        'total_exchanges',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rating' => 'decimal:2',
        ];
    }

    /**
     * Skills offered by the user
     */
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Exchanges initiated by the user
     */
    public function initiatedExchanges()
    {
        return $this->hasMany(Exchange::class, 'initiator_id');
    }

    /**
     * Exchanges received by the user
     */
    public function receivedExchanges()
    {
        return $this->hasMany(Exchange::class, 'receiver_id');
    }

    /**
     * All exchanges (initiated and received)
     */
    public function exchanges()
    {
        return $this->initiatedExchanges()->union($this->receivedExchanges());
    }

    /**
     * Messages sent by the user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Messages received by the user
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
