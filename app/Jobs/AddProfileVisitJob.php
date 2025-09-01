<?php

namespace App\Jobs;

use App\Mail\ProfileVisitMail;
use App\Models\ProfileVisits;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class AddProfileVisitJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $userName;
    protected $visitedId;
    protected $visitedMail;

    public function __construct($userId, $userName, $visitedId, $visitedMail)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->visitedId = $visitedId;
        $this->visitedMail = $visitedMail;
    }

    public function handle()
    {

        $alreadyMailSendedtoday = ProfileVisits::where('visited_id', $this->visitedId)->whereDate('mail_send_at', today())->exists();
        $profileVisit = ProfileVisits::create([
            'user_id' => $this->userId,
            'visited_id' => $this->visitedId
        ]);
        if (!$alreadyMailSendedtoday) {
            Mail::to($this->visitedMail)->queue(new ProfileVisitMail());
            $profileVisit->update(['mail_send_at' => now()]);
        }
    }
}
