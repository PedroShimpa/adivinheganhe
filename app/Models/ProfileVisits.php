<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileVisits extends Model
{
    protected $table = 'profile_visits';

    protected $fillable = [
        'user_id',
        'visited_id',
        'mail_send_at'
    ];
}
