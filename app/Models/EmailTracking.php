<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTracking extends Model
{
    protected $table = 'email_tracking';

    protected $fillable = [
        'email',
        'subject',
        'tracking_id',
        'sent_at',
        'opened_at',
        'clicked_links'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_links' => 'array'
    ];
}
