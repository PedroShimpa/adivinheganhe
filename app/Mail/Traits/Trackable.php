<?php

namespace App\Mail\Traits;

use App\Models\EmailTracking;
use Illuminate\Support\Str;

trait Trackable
{
    protected $trackingId;

    public function track($email, $subject)
    {
        $this->trackingId = Str::uuid();

        EmailTracking::create([
            'email' => $email,
            'subject' => $subject,
            'tracking_id' => $this->trackingId,
            'sent_at' => now(),
        ]);

        return $this;
    }

    protected function buildTrackingPixel()
    {
        if (!$this->trackingId) {
            return '';
        }

        $trackingUrl = route('email_tracking.open', $this->trackingId);
        return "<img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";
    }

    protected function trackableLink($url, $linkText)
    {
        if (!$this->trackingId) {
            return "<a href=\"{$url}\">{$linkText}</a>";
        }

        $trackingUrl = route('email_tracking.click', ['trackingId' => $this->trackingId, 'url' => urlencode($url)]);
        return "<a href=\"{$trackingUrl}\">{$linkText}</a>";
    }
}
