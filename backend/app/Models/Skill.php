<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'level',
        'is_available',
        'image',
        'tags',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * User who owns this skill
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category this skill belongs to
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Exchanges where this skill is offered
     */
    public function offeredExchanges()
    {
        return $this->hasMany(Exchange::class, 'offered_skill_id');
    }

    /**
     * Exchanges where this skill is requested
     */
    public function requestedExchanges()
    {
        return $this->hasMany(Exchange::class, 'requested_skill_id');
    }
}
