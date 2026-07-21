<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, HasPublicUuid;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
    ];

    /**
     * Skills belonging to this category
     */
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }
}
