<?php

namespace App\Models;

use Database\Factories\PracticeProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeProfile extends Model
{
    /** @use HasFactory<PracticeProfileFactory> */
    use HasFactory;

    protected $fillable = ['started_on', 'timezone'];

    protected function casts(): array
    {
        return ['started_on' => 'date'];
    }
}
