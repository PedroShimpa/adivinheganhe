<?php

namespace App\Http\Controllers;

use App\Models\EmailTracking;
use Illuminate\Http\Request;

class EmailTrackingController extends Controller
{
    public function index()
    {
        $trackings = EmailTracking::orderBy('sent_at', 'desc')->paginate(20);
        return view('admin.email_tracking.index', compact('trackings'));
    }

    public function trackOpen(Request $request, $trackingId)
    {
        $tracking = EmailTracking::where('tracking_id', $trackingId)->first();

        if ($tracking && !$tracking->opened_at) {
            $tracking->update(['opened_at' => now()]);
        }

        // Return a 1x1 transparent pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel)->header('Content-Type', 'image/gif');
    }

    public function trackClick(Request $request, $trackingId)
    {
        $tracking = EmailTracking::where('tracking_id', $trackingId)->first();
        $url = $request->get('url');

        if ($tracking && $url) {
            $clickedLinks = $tracking->clicked_links ?? [];
            $clickedLinks[] = [
                'url' => $url,
                'clicked_at' => now()->toISOString()
            ];
            $tracking->update(['clicked_links' => $clickedLinks]);
        }

        // Redirect to the actual URL
        return redirect($url);
    }
}
