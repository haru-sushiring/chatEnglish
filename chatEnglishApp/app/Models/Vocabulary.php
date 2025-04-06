<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vocabulary extends Model
{
    use HasFactory;

    protected $table = 'vocabulary';

    protected $fillable = [
        'word',
        'meaning',
        'user_id',
        'notification_date',
        'test_notification_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
