<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'initiator_id',
        'receiver_id',
        'offered_skill_id',
        'requested_skill_id',
        'status',
        'message',
        'scheduled_at',
        'completed_at',
        'rating_initiator',
        'rating_receiver',
        'feedback_initiator',
        'feedback_receiver',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'rating_initiator' => 'integer',
        'rating_receiver' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * User who initiated the exchange
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * User who received the exchange request
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Skill being offered in the exchange
     */
    public function offeredSkill()
    {
        return $this->belongsTo(Skill::class, 'offered_skill_id');
    }

    /**
     * Skill being requested in the exchange
     */
    public function requestedSkill()
    {
        return $this->belongsTo(Skill::class, 'requested_skill_id');
    }

    /**
     * Messages related to this exchange
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
