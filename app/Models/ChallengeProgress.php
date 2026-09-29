<?php

namespace App\Models;

use Database\Factories\ChallengeProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeProgress extends Model
{
    /** @use HasFactory<ChallengeProgressFactory> */
    use HasFactory;

    protected $fillable = ['day', 'draft_sql', 'attempts', 'completed_at', 'completed_on_time'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime', 'completed_on_time' => 'boolean'];
    }
}
